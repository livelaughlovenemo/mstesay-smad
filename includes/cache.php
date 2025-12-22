<?php
// includes/cache.php

/**
 * CacheManager - Handles caching of database queries and reports
 */
class CacheManager {
    private $pdo;
    private $cacheTable = 'cache';
    private $defaultTTL = 3600; // 1 hour in seconds
    private $enabled = true;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initializeCacheTable();
        $this->cleanExpiredCache();
    }
    
    /**
     * Create cache table if it doesn't exist
     */
    private function initializeCacheTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->cacheTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cache_key VARCHAR(255) NOT NULL UNIQUE,
                cache_type VARCHAR(50) NOT NULL,
                data LONGTEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                hits INT DEFAULT 0,
                parameters TEXT,
                INDEX idx_cache_key (cache_key),
                INDEX idx_expires_at (expires_at),
                INDEX idx_cache_type (cache_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->pdo->exec($sql);
        } catch (Exception $e) {
            error_log("Failed to initialize cache table: " . $e->getMessage());
            $this->enabled = false;
        }
    }
    
    /**
     * Clean expired cache entries
     */
    private function cleanExpiredCache() {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM {$this->cacheTable} 
                WHERE expires_at < NOW()
            ");
            $stmt->execute();
            
            // Also clean old cache if table gets too large (optional)
            $this->cleanOldCache();
        } catch (Exception $e) {
            error_log("Failed to clean expired cache: " . $e->getMessage());
        }
    }
    
    /**
     * Clean old cache entries to prevent table from growing too large
     */
    private function cleanOldCache($maxEntries = 1000) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM {$this->cacheTable}
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > $maxEntries) {
                $limit = $result['count'] - $maxEntries;
                $stmt = $this->pdo->prepare("
                    DELETE FROM {$this->cacheTable}
                    ORDER BY created_at ASC
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
            }
        } catch (Exception $e) {
            error_log("Failed to clean old cache: " . $e->getMessage());
        }
    }
    
    /**
     * Get cached data for a report
     */
    public function getCachedReport($type, $params = []) {
        if (!$this->enabled) {
            return null;
        }
        
        try {
            $cacheKey = $this->generateCacheKey($type, $params);
            
            $stmt = $this->pdo->prepare("
                SELECT data, hits, created_at 
                FROM {$this->cacheTable} 
                WHERE cache_key = ? 
                AND cache_type = ?
                AND expires_at > NOW()
                LIMIT 1
            ");
            
            $stmt->execute([$cacheKey, $type]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Update hit counter
                $this->incrementHitCount($cacheKey);
                
                // Return decoded data
                return json_decode($row['data'], true);
            }
        } catch (Exception $e) {
            error_log("Cache retrieval error: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Cache a report with data
     */
    public function cacheReport($type, $data, $params = [], $ttl = null) {
        if (!$this->enabled) {
            return false;
        }
        
        try {
            $cacheKey = $this->generateCacheKey($type, $params);
            $dataJson = json_encode($data);
            $paramsJson = json_encode($params);
            
            if ($ttl === null) {
                $ttl = $this->defaultTTL;
            }
            
            $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->cacheTable} 
                (cache_key, cache_type, data, parameters, expires_at, hits, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())
                ON DUPLICATE KEY UPDATE 
                data = VALUES(data),
                parameters = VALUES(parameters),
                expires_at = VALUES(expires_at),
                hits = 0,
                created_at = NOW()
            ");
            
            return $stmt->execute([
                $cacheKey, 
                $type, 
                $dataJson, 
                $paramsJson,
                $expiresAt
            ]);
        } catch (Exception $e) {
            error_log("Cache storage error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate a unique cache key
     */
    private function generateCacheKey($type, $params) {
        // Normalize parameters for consistent key generation
        ksort($params);
        $paramString = serialize($params);
        return $type . '_' . md5($paramString);
    }
    
    /**
     * Increment hit counter for a cache entry
     */
    private function incrementHitCount($cacheKey) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->cacheTable} 
                SET hits = hits + 1 
                WHERE cache_key = ?
            ");
            $stmt->execute([$cacheKey]);
        } catch (Exception $e) {
            error_log("Failed to increment hit count: " . $e->getMessage());
        }
    }
    
    /**
     * Clear all cache
     */
    public function clearAllCache() {
        try {
            $stmt = $this->pdo->prepare("TRUNCATE TABLE {$this->cacheTable}");
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to clear cache: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear cache by type
     */
    public function clearCacheByType($type) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM {$this->cacheTable} 
                WHERE cache_type = ?
            ");
            return $stmt->execute([$type]);
        } catch (Exception $e) {
            error_log("Failed to clear cache by type: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear cache by key pattern
     */
    public function clearCacheByPattern($pattern) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM {$this->cacheTable} 
                WHERE cache_key LIKE ?
            ");
            return $stmt->execute(["%{$pattern}%"]);
        } catch (Exception $e) {
            error_log("Failed to clear cache by pattern: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getCacheStats() {
        try {
            $stats = [];
            
            // Total cache entries
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM {$this->cacheTable}");
            $stmt->execute();
            $stats['total_entries'] = $stmt->fetch()['total'];
            
            // Expired entries
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as expired FROM {$this->cacheTable} WHERE expires_at < NOW()");
            $stmt->execute();
            $stats['expired_entries'] = $stmt->fetch()['expired'];
            
            // Total hits
            $stmt = $this->pdo->prepare("SELECT SUM(hits) as total_hits FROM {$this->cacheTable}");
            $stmt->execute();
            $stats['total_hits'] = $stmt->fetch()['total_hits'] ?? 0;
            
            // Cache by type
            $stmt = $this->pdo->prepare("SELECT cache_type, COUNT(*) as count, SUM(hits) as hits FROM {$this->cacheTable} GROUP BY cache_type");
            $stmt->execute();
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Failed to get cache stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Enable/disable cache
     */
    public function setEnabled($enabled) {
        $this->enabled = (bool)$enabled;
    }
    
    /**
     * Set default TTL
     */
    public function setDefaultTTL($ttl) {
        $this->defaultTTL = (int)$ttl;
    }
}
?>
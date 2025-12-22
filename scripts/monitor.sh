#!/bin/bash

# System monitoring script for SMAD

set -e

# Configuration
LOG_DIR="/var/log/smad"
ALERT_EMAIL="admin@mstesaychicken.com"
THRESHOLD_CPU=80
THRESHOLD_MEMORY=80
THRESHOLD_DISK=85

# Functions
check_cpu() {
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
    echo "CPU Usage: ${cpu_usage}%"
    
    if (( $(echo "$cpu_usage > $THRESHOLD_CPU" | bc -l) )); then
        send_alert "High CPU Usage" "CPU usage is at ${cpu_usage}%"
    fi
}

check_memory() {
    local mem_total=$(free -m | awk '/^Mem:/{print $2}')
    local mem_used=$(free -m | awk '/^Mem:/{print $3}')
    local mem_percent=$((mem_used * 100 / mem_total))
    
    echo "Memory Usage: ${mem_percent}% (${mem_used}MB/${mem_total}MB)"
    
    if [ $mem_percent -gt $THRESHOLD_MEMORY ]; then
        send_alert "High Memory Usage" "Memory usage is at ${mem_percent}%"
    fi
}

check_disk() {
    local disk_usage=$(df -h / | awk 'NR==2 {print $5}' | cut -d'%' -f1)
    
    echo "Disk Usage: ${disk_usage}%"
    
    if [ $disk_usage -gt $THRESHOLD_DISK ]; then
        send_alert "High Disk Usage" "Disk usage is at ${disk_usage}%"
    fi
}

check_database() {
    # Check database connections
    local connections=$(mysql -u root -e "SHOW PROCESSLIST" | wc -l)
    echo "Database Connections: $((connections - 1))"
    
    # Check for slow queries
    local slow_queries=$(mysql -u root -e "SHOW STATUS LIKE 'Slow_queries'" | awk 'NR==2 {print $2}')
    echo "Slow Queries: $slow_queries"
}

check_application() {
    # Check if web server is responding
    local response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health-check)
    
    if [ "$response" != "200" ]; then
        send_alert "Application Down" "HTTP response: $response"
    fi
}

check_logs() {
    # Check for errors in logs
    local error_count=$(grep -c "ERROR\|FATAL" "$LOG_DIR/error.log" 2>/dev/null || echo 0)
    
    if [ $error_count -gt 0 ]; then
        echo "Recent Errors: $error_count"
        
        # Get last 5 errors
        local recent_errors=$(grep "ERROR\|FATAL" "$LOG_DIR/error.log" | tail -5)
        send_alert "Application Errors" "Found $error_count errors\n\nRecent errors:\n$recent_errors"
    fi
}

send_alert() {
    local subject="$1"
    local message="$2"
    
    echo "ALERT: $subject"
    echo "$message"
    
    # Send email alert
    echo "$message" | mail -s "[SMAD Alert] $subject" "$ALERT_EMAIL"
    
    # Log alert
    echo "[$(date)] ALERT: $subject" >> "$LOG_DIR/alerts.log"
}

# Main monitoring function
main() {
    echo "🔍 SMAD System Monitoring - $(date)"
    echo "=================================="
    
    check_cpu
    check_memory
    check_disk
    check_database
    check_application
    check_logs
    
    echo "=================================="
    echo "✅ Monitoring completed"
}

# Run monitoring
main "$@"
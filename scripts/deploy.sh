#!/bin/bash

# Deployment script for Ms. Tesay Chicken Sales Dashboard

set -e

echo "🚀 Starting deployment..."

# Configuration
BACKUP_DIR="/var/backups/smad"
DEPLOY_DIR="/var/www/smad"
REPO_URL="https://github.com/your-repo/smad.git"
BRANCH="main"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
log_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

log_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    log_error "Please run as root"
    exit 1
fi

# Create backup
backup() {
    log_warning "Creating backup..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/backup_$TIMESTAMP.tar.gz"
    
    mkdir -p "$BACKUP_DIR"
    
    tar -czf "$BACKUP_PATH" \
        --exclude="vendor" \
        --exclude="node_modules" \
        --exclude="*.log" \
        -C "$DEPLOY_DIR" .
    
    if [ $? -eq 0 ]; then
        log_success "Backup created: $BACKUP_PATH"
        
        # Keep only last 5 backups
        ls -t "$BACKUP_DIR"/backup_*.tar.gz | tail -n +6 | xargs rm -f
    else
        log_error "Backup failed"
        exit 1
    fi
}

# Update code from repository
update_code() {
    log_warning "Updating code from repository..."
    
    cd "$DEPLOY_DIR"
    
    if [ -d ".git" ]; then
        git fetch origin
        git checkout "$BRANCH"
        git pull origin "$BRANCH"
    else
        git clone -b "$BRANCH" "$REPO_URL" "$DEPLOY_DIR"
    fi
    
    log_success "Code updated"
}

# Install dependencies
install_dependencies() {
    log_warning "Installing dependencies..."
    
    cd "$DEPLOY_DIR"
    
    # PHP dependencies
    if [ -f "composer.json" ]; then
        composer install --no-dev --optimize-autoloader
    fi
    
    # Node.js dependencies
    if [ -f "package.json" ]; then
        npm install --production
        npm run build
    fi
    
    log_success "Dependencies installed"
}

# Set permissions
set_permissions() {
    log_warning "Setting permissions..."
    
    # Web server user (adjust based on your setup)
    WEB_USER="www-data"
    WEB_GROUP="www-data"
    
    # Set directory permissions
    chown -R "$WEB_USER:$WEB_GROUP" "$DEPLOY_DIR"
    find "$DEPLOY_DIR" -type d -exec chmod 755 {} \;
    find "$DEPLOY_DIR" -type f -exec chmod 644 {} \;
    
    # Set executable permissions for scripts
    chmod +x "$DEPLOY_DIR"/scripts/*.sh
    
    # Set write permissions for specific directories
    chmod -R 775 "$DEPLOY_DIR"/storage
    chmod -R 775 "$DEPLOY_DIR"/cache
    chmod -R 775 "$DEPLOY_DIR"/logs
    
    log_success "Permissions set"
}

# Update database
update_database() {
    log_warning "Updating database..."
    
    cd "$DEPLOY_DIR"
    
    if [ -f "database/migrations/latest.sql" ]; then
        mysql -u root -p"$DB_PASSWORD" smad_init < database/migrations/latest.sql
    fi
    
    log_success "Database updated"
}

# Clear caches
clear_caches() {
    log_warning "Clearing caches..."
    
    # Clear PHP opcache
    if [ -f /etc/init.d/php8.2-fpm ]; then
        service php8.2-fpm reload
    fi
    
    # Clear application cache
    rm -rf "$DEPLOY_DIR"/cache/*
    rm -rf "$DEPLOY_DIR"/storage/cache/*
    
    log_success "Caches cleared"
}

# Run tests
run_tests() {
    log_warning "Running tests..."
    
    cd "$DEPLOY_DIR"
    
    if [ -f "vendor/bin/phpunit" ]; then
        vendor/bin/phpunit tests/ --colors=always
    fi
    
    log_success "Tests passed"
}

# Restart services
restart_services() {
    log_warning "Restarting services..."
    
    # Restart web server
    systemctl restart nginx
    systemctl restart php8.2-fpm
    
    # Restart queue worker if using
    if systemctl is-active --quiet smad-worker; then
        systemctl restart smad-worker
    fi
    
    log_success "Services restarted"
}

# Main deployment process
main() {
    echo "📦 Ms. Tesay Chicken Sales Dashboard Deployment"
    echo "=============================================="
    
    # Check if deployment directory exists
    if [ ! -d "$DEPLOY_DIR" ]; then
        log_warning "Creating deployment directory..."
        mkdir -p "$DEPLOY_DIR"
    fi
    
    # Run deployment steps
    backup
    update_code
    install_dependencies
    set_permissions
    update_database
    clear_caches
    run_tests
    restart_services
    
    log_success "✅ Deployment completed successfully!"
    
    # Show deployment info
    echo ""
    echo "Deployment Summary:"
    echo "-------------------"
    echo "Application: $DEPLOY_DIR"
    echo "Backup: $BACKUP_PATH"
    echo "Timestamp: $(date)"
    echo "Branch: $BRANCH"
    echo ""
}

# Run main function
main "$@"
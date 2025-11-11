#!/bin/bash

# Quick Setup Script for Aussie Band Merch Dropship Plugin
# This script installs Composer dependencies

set -e

echo "========================================="
echo "Aussie Band Merch Dropship - Setup"
echo "========================================="
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "ERROR: Composer is not installed."
    echo ""
    echo "Please install Composer first:"
    echo "  https://getcomposer.org/download/"
    echo ""
    exit 1
fi

echo "✓ Composer found"
echo ""

# Check if we're in the plugin directory
if [ ! -f "aussie-band-merch-dropship.php" ]; then
    echo "ERROR: This script must be run from the plugin directory"
    exit 1
fi

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================="
    echo "✓ Setup Complete!"
    echo "========================================="
    echo ""
    echo "Next steps:"
    echo "1. Activate the plugin in WordPress Admin"
    echo "2. Go to Band Merch → Settings"
    echo "3. Configure your markup percentage"
    echo "4. Start importing products!"
    echo ""
else
    echo ""
    echo "ERROR: Composer install failed"
    exit 1
fi

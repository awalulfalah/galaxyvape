#!/bin/bash
# Buat folder uploads jika belum ada
mkdir -p uploads/products

# Set permission yang benar
chmod -R 755 uploads
chown -R www-data:www-data uploads  # Sesuaikan dengan user web server Anda

# Buat .htaccess di folder uploads
echo "Options -Indexes
Allow from all" > uploads/.htaccess 
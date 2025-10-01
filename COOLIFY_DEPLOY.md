# Coolify Deployment Guide - External MySQL

This guide shows how to deploy your Laravel application on Coolify using an external MySQL database.

## 🗄️ Step 1: Set Up MySQL Database

### Option A: Coolify Managed MySQL
1. In Coolify, create a new **MySQL** service
2. Note the connection details:
   - Host: `mysql-service-name`
   - Port: `3306`
   - Database: `dididcrawler`
   - Username: Your choice (e.g., `dididuser`)
   - Password: Auto-generated secure password

### Option B: External MySQL Provider
Use services like:
- **PlanetScale** (Recommended for Laravel)
- **AWS RDS**
- **Google Cloud SQL**
- **DigitalOcean Managed Database**

## 🚀 Step 2: Deploy Laravel Application

### 2.1 Create Application in Coolify
1. **Add new resource** → **Application**
2. **Connect your Git repository**
3. **Build pack**: Docker
4. **Dockerfile path**: `./Dockerfile`
5. **Port**: 80

### 2.2 Environment Variables
Set these environment variables in Coolify:

```bash
# Application Settings
APP_NAME="Didid Crawler"
APP_ENV=production
APP_KEY=base64:JIan8waFhv4Bt4U3e4+hzxNiHE4QW9ty3feqsHR/n1U=
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=dididcrawler
DB_USERNAME=your-username
DB_PASSWORD=your-secure-password

# Optional: Seed database on first deployment
SEED_DATABASE=true

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail (configure as needed)
MAIL_MAILER=log
```

### 2.3 Generate APP_KEY
If you need a new application key:
```bash
php artisan key:generate --show
```

## 📋 Step 3: Database Setup

### 3.1 Create Database
If using external MySQL, create the database:
```sql
CREATE DATABASE dididcrawler CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3.2 Create User (if needed)
```sql
CREATE USER 'dididuser'@'%' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON dididcrawler.* TO 'dididuser'@'%';
FLUSH PRIVILEGES;
```

## 🔧 Step 4: Deploy

1. **Deploy the application** in Coolify
2. The entrypoint script will automatically:
   - Wait for database connection
   - Run migrations
   - Seed database (if enabled)
   - Cache configuration
   - Start Apache

## 📊 Step 5: Access & Test

1. **Access your application** at the assigned URL
2. **Test login credentials**:
   - **Superadmin**: `admin@dididcrawler.com` / `password123`
   - **User**: `budi@example.com` / `userpass123`

## 🛠️ Troubleshooting

### Database Connection Issues
1. **Check environment variables** are correctly set
2. **Verify database host** is accessible from Coolify
3. **Check firewall rules** if using external database
4. **View application logs** in Coolify

### Migration Issues
1. **Ensure database exists** and is accessible
2. **Check user permissions** for database operations
3. **Manually run migrations** if needed:
   ```bash
   php artisan migrate --force
   ```

### Performance Optimization
1. **Enable opcache** in production
2. **Use Redis** for session/cache (optional)
3. **Set up CDN** for assets
4. **Configure proper PHP limits**

## 🔒 Security Considerations

1. **Use strong passwords** for database
2. **Restrict database access** to Coolify IPs only
3. **Enable SSL** for database connections
4. **Keep APP_KEY secure** and unique
5. **Set APP_DEBUG=false** in production

## 📁 File Structure
Your deployment includes:
- `Dockerfile` - Container configuration
- `docker/entrypoint.sh` - Startup script
- `docker/apache.conf` - Web server config
- `.dockerignore` - Build optimization

## 🔄 Updates & Maintenance

### Deploying Updates
1. **Push changes** to your Git repository
2. **Redeploy** in Coolify
3. **Monitor logs** for any issues

### Database Backups
- **Coolify managed**: Automatic backups
- **External provider**: Use provider's backup features
- **Manual backup**: Use `mysqldump` regularly

## 📞 Support

If you encounter issues:
1. Check Coolify application logs
2. Review database connection settings
3. Verify environment variables
4. Test database connectivity

---

**Your Laravel application is now production-ready with external MySQL on Coolify!** 🎉
# Docker Deployment Guide

This Laravel application is containerized and ready for deployment on Coolify or any Docker-compatible platform.

## Files Created for Docker Deployment

- `Dockerfile` - Main Docker configuration
- `docker/apache.conf` - Apache virtual host configuration
- `docker/entrypoint.sh` - Application initialization script
- `.dockerignore` - Files to exclude from Docker build
- `.env.production` - Production environment template
- `docker-compose.yml` - Local testing configuration

## Deployment on Coolify

### Prerequisites
1. Coolify instance running
2. Git repository with your Laravel application
3. Domain name (optional but recommended)

### Deployment Steps

1. **Connect Repository**: In Coolify, add your Git repository as a new application.

2. **Environment Variables**: Set up the following environment variables in Coolify:
   ```
   APP_NAME=Your App Name
   APP_ENV=production
   APP_KEY=base64:YOUR_GENERATED_KEY_HERE
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=your-mysql-host
   DB_PORT=3306
   DB_DATABASE=dididcrawler
   DB_USERNAME=dididuser
   DB_PASSWORD=your_secure_password_here
   
   SEED_DATABASE=true  # Set to true for initial deployment only
   ```

3. **Generate APP_KEY**: You can generate an APP_KEY locally by running:
   ```bash
   php artisan key:generate --show
   ```

4. **Build Configuration**: 
   - Build Pack: Docker
   - Dockerfile Path: `./Dockerfile`
   - Port: 80

5. **Persistent Storage** (Optional): If you need persistent data, mount volumes for:
   - `/var/www/html/storage` - For logs and cache
   - `/var/www/html/database/database.sqlite` - For SQLite database

### Local Testing

Test the Docker setup locally:

```bash
# Build and run with docker-compose
docker-compose up --build

# Or build and run manually
docker build -t laravel-app .
docker run -p 8080:80 -e APP_KEY=base64:YOUR_KEY_HERE laravel-app
```

Access the application at `http://localhost:8080`

## Environment Variables Reference

### Required Variables
- `APP_KEY` - Application encryption key (generate with `php artisan key:generate --show`)
- `APP_URL` - Your application URL

### Optional Variables
- `SEED_DATABASE=true` - Run database seeders on startup (use only for initial deployment)
- `APP_DEBUG=false` - Set to true only for debugging
- `DB_CONNECTION=sqlite` - Database connection (default: sqlite)

### Database Options

**MySQL (Recommended for Production)**:
```
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=dididcrawler
DB_USERNAME=dididuser
DB_PASSWORD=your_secure_password_here
```

**SQLite** (for simple deployments):
```
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

**PostgreSQL**:
```
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=dididcrawler
DB_USERNAME=dididuser
DB_PASSWORD=your_secure_password_here
```

## Troubleshooting

### Common Issues

1. **Permission Errors**: The entrypoint script sets proper permissions automatically.

2. **Database Not Found**: Ensure the SQLite file exists or database credentials are correct.

3. **Asset Loading Issues**: Make sure `APP_URL` matches your actual domain.

4. **Key Generation Issues**: Generate the APP_KEY locally and set it as an environment variable.

### Logs

Check application logs in Coolify or run:
```bash
docker logs <container_name>
```

### Manual Commands

If you need to run artisan commands manually:
```bash
docker exec -it <container_name> php artisan <command>
```

## Security Notes

- Never commit `.env` files with real credentials
- Use strong, unique APP_KEY for production
- Keep APP_DEBUG=false in production
- Consider using external databases for better data persistence
- Set up proper backup strategies for your data
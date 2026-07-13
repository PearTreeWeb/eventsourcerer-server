# System Requirements

This document outlines the system requirements for running Event Sourcerer effectively.

## Minimum Requirements

### Server Requirements
- **Operating System**: Linux, macOS, or Windows
- **PHP**: Version 8.3 or higher
- **Memory**: 2GB RAM minimum (4GB recommended)
- **Storage**: 1GB free disk space minimum
- **Database**: PostgreSQL 12+ (recommended) or MySQL 8.0+

### Development Environment
- **Composer**: Latest version for PHP dependency management
- **Node.js**: Version 16+ for asset compilation
- **npm**: For JavaScript package management
- **Symfony CLI**: For local development server (optional but recommended)

### Browser Support
- **Chrome**: Version 90+
- **Firefox**: Version 88+
- **Safari**: Version 14+
- **Edge**: Version 90+

## Recommended Production Requirements

### Server Specifications
- **CPU**: 4+ cores
- **Memory**: 8GB RAM
- **Storage**: SSD with 50GB+ free space
- **Network**: Stable internet connection with sufficient bandwidth for real-time streaming

### Database Configuration
- **PostgreSQL**: Version 14+ with optimized configuration
- **Connection Pool**: Configured for concurrent connections
- **Backup Strategy**: Regular automated backups

### Additional Production Considerations
- **Load Balancer**: For high-availability deployments
- **SSL/TLS**: Valid certificates for secure connections
- **Monitoring**: Application and infrastructure monitoring tools
- **Logging**: Centralized logging system

## PHP Extensions

Required PHP extensions:
- `ext-ctype`
- `ext-iconv`
- `ext-json`
- `ext-pdo`
- `ext-pdo_pgsql` (for PostgreSQL) or `ext-pdo_mysql` (for MySQL)
- `ext-mbstring`
- `ext-openssl`
- `ext-curl`
- `ext-xml`
- `ext-zip`

## Network Requirements

### Ports
- **HTTP**: Port 80 (or custom port for development)
- **HTTPS**: Port 443 (production)
- **Socket Server**: Configurable port (default varies)
- **Database**: Standard database ports (5432 for PostgreSQL, 3306 for MySQL)

### Firewall Configuration
- Allow inbound connections on HTTP/HTTPS ports
- Allow database connections from application servers
- Configure socket server port accessibility as needed

## Development Tools

### Required
- **Git**: Version control system
- **Composer**: PHP dependency manager
- **npm/yarn**: JavaScript package managers

### Recommended
- **Docker**: For containerized development
- **Symfony CLI**: Local development server
- **PHPUnit**: Testing framework
- **IDE/Editor**: PhpStorm, VS Code, or similar with PHP support

## Performance Considerations

### For Small Deployments (< 1000 events/day)
- 2 CPU cores
- 4GB RAM
- Standard SSD storage

### For Medium Deployments (1000-10,000 events/day)
- 4 CPU cores
- 8GB RAM
- High-performance SSD
- Database optimization

### For Large Deployments (> 10,000 events/day)
- 8+ CPU cores
- 16GB+ RAM
- NVMe SSD storage
- Dedicated database server
- Redis/Memcached for caching
- Load balancing
- Database replication

## Security Requirements

### Minimum Security Measures
- Keep PHP and all dependencies up to date
- Use HTTPS in production
- Implement proper database security
- Regular security audits
- Strong password policies

### Recommended Security Enhancements
- Web Application Firewall (WAF)
- DDoS protection
- Intrusion detection system
- Regular penetration testing
- Security monitoring and alerting

## Scalability Planning

### Horizontal Scaling
- Load balancers
- Multiple application instances
- Database read replicas
- Distributed caching

### Vertical Scaling
- Increased CPU/memory resources
- Faster storage solutions
- Network bandwidth upgrades

## Troubleshooting Common Issues

### Performance Issues
- Check PHP memory limits
- Optimize database queries
- Monitor system resources
- Review application logs

### Connection Issues
- Verify network connectivity
- Check firewall settings
- Validate SSL certificates
- Test socket server connectivity

For detailed troubleshooting, see the [Troubleshooting Guide](17-troubleshooting.md).

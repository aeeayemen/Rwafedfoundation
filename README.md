# Rawafed Yemen Website - PHP Backend

A complete PHP backend solution for the Rawafed Yemen website with multilingual support (Arabic/English) and admin panel.

## Features

- **Multilingual Support**: Full Arabic and English language support
- **Admin Panel**: Complete content management system
- **Dynamic Content**: Database-driven content management
- **Responsive Design**: Mobile-friendly interface
- **Security**: CSRF protection, input sanitization, and secure authentication
- **File Upload**: Image upload functionality for content and news
- **SEO Friendly**: Clean URLs and meta tags

## Project Structure

```
project/
├── admin/                  # Admin panel files
│   ├── index.php          # Dashboard
│   ├── login.php          # Admin login
│   ├── logout.php         # Logout functionality
│   ├── sections.php       # Content sections management
│   ├── edit_section.php   # Edit content sections
│   ├── news.php           # News management
│   ├── edit_news.php      # Edit news articles
│   ├── partners.php       # Partners management
│   └── edit_partner.php   # Edit partners
├── includes/              # Common files
│   ├── db.php            # Database connection
│   ├── auth.php          # Authentication functions
│   ├── functions.php     # Helper functions
│   ├── header.php        # Common header
│   └── footer.php        # Common footer
├── uploads/              # Upload directories
│   ├── sections/         # Section images
│   ├── news/            # News images
│   └── partners/        # Partner logos
├── index.php            # Home page
├── about.php            # About page
├── mission.php          # Mission page
├── projects.php         # Projects page
├── news.php             # News listing
├── partners.php         # Partners page
└── database.sql         # Database structure
```

## Installation

### 1. Database Setup

1. Create a MySQL database named `rawafed_db`
2. Import the database structure:
   ```sql
   mysql -u username -p rawafed_db < database.sql
   ```

### 2. Configuration

1. Update database credentials in `includes/db.php`:
   ```php
   $host = 'localhost';
   $dbname = 'rawafed_db';
   $username = 'your_username';
   $password = 'your_password';
   ```

### 3. File Permissions

Set proper permissions for upload directories:
```bash
chmod 755 uploads/
chmod 755 uploads/sections/
chmod 755 uploads/news/
chmod 755 uploads/partners/
```

### 4. Web Server Configuration

Ensure your web server supports:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- mod_rewrite (for clean URLs)

## Default Admin Credentials

- **Username**: admin
- **Password**: admin123

**Important**: Change these credentials immediately after installation!

## Database Tables

### sections
Stores content for different page sections with multilingual support.

### news
Manages news articles with English and Arabic content.

### partners
Stores partner information and logos.

### users
Admin user accounts with authentication.

### projects
Project information and details.

## Language Support

The system automatically detects and switches between Arabic and English based on:
- URL parameter: `?lang=ar` or `?lang=en`
- Session storage for persistence
- Default language: English

## Admin Panel Features

### Content Management
- Edit page sections (Home, About, Mission, etc.)
- Multilingual content editing
- Image upload for sections

### News Management
- Create, edit, delete news articles
- Multilingual news content
- Featured image upload
- Publication status control

### Partners Management
- Add/edit partner information
- Logo upload
- Website links
- Status management

### Projects Management
- Project information management
- Status tracking (ongoing, completed, planned)
- Location and budget tracking

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **Input Sanitization**: All user inputs are sanitized
- **File Upload Security**: File type and size validation
- **SQL Injection Prevention**: Prepared statements used throughout
- **Session Management**: Secure session handling

## File Upload Guidelines

### Supported Formats
- Images: JPG, JPEG, PNG, GIF
- Maximum file size: 5MB

### Upload Directories
- Section images: `uploads/sections/`
- News images: `uploads/news/`
- Partner logos: `uploads/partners/`

## Customization

### Adding New Languages
1. Add language columns to database tables
2. Update `getCurrentLanguage()` function in `includes/functions.php`
3. Add language option in header language selector

### Adding New Content Sections
1. Insert new records in the `sections` table
2. Update admin panel section management
3. Add content display in frontend pages

### Styling
- Main CSS file: `css/style.css`
- Bootstrap 5 framework included
- FontAwesome icons available

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/db.php`
   - Ensure MySQL service is running

2. **File Upload Issues**
   - Check directory permissions
   - Verify PHP upload settings (`upload_max_filesize`, `post_max_size`)

3. **Session Issues**
   - Ensure session directory is writable
   - Check PHP session configuration

4. **Language Switching Not Working**
   - Verify session is started in all pages
   - Check language parameter handling

## Development Notes

### Code Standards
- PSR-4 autoloading structure
- Consistent naming conventions
- Comprehensive error handling
- Security-first approach

### Performance Considerations
- Database queries are optimized
- Image optimization recommended
- Caching can be implemented for better performance

## Support

For technical support or questions:
- Email: info@rsd-yemen.org
- Documentation: Check inline code comments

## License

This project is developed for Rawafed Yemen Foundation. All rights reserved.

---

**Note**: This is a pure PHP implementation. For production use, consider using a PHP framework like Laravel for enhanced security and maintainability.


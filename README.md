# School Inventory Management System

A web-based system for schools to manage their inventory of books, uniforms, and other educational supplies. The system allows schools to track their inventory and enables parents to view and order items for their children.

## Features

- **Modern, Responsive Interface**: A clean, professional UI that works on all devices
- **Two User Portals**: Separate login systems for schools and parents
- **School Dashboard**: Inventory management, order tracking, and sales reporting
- **Parent Dashboard**: Order placement, tracking, and viewing available inventory
- **Secure Authentication**: Password hashing and session management
- **Interactive Elements**: Real-time feedback, animations, and intuitive controls
- **Account Recovery**: Forgot password functionality for both user types

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **SVG Graphics**: Custom illustrations for improved user experience
- **Libraries**: Font Awesome 5, Animate.css

## Installation

1. Clone the repository to your local machine or server
2. Ensure PHP 7.4 or higher is installed on your server
3. Set up a web server (Apache/Nginx) to serve the PHP files
4. (For production) Set up a MySQL database and configure connection details
5. (For production) Configure email settings for password reset functionality
6. Navigate to the index.html page in your web browser

## Project Structure

```
├── index.html                  # Homepage with login options
├── parent_login.php            # Parent login page
├── school_login.php            # School login page
├── forgot_password.php         # Password reset page
├── parent_dashboard.php        # Parent dashboard
├── school_dashboard.php        # School dashboard
├── logout.php                  # Logout functionality
├── css/
│   └── styles.css              # Main stylesheet
├── js/
│   └── main.js                 # JavaScript functionality
├── img/
│   ├── logo.svg                # System logo
│   ├── inventory.svg           # Inventory illustration
│   ├── parent_child.svg        # Parent illustration
│   └── school_illustration.svg # School illustration
└── README.md                   # This file
```

## Demo Credentials

### School Login
- School ID: SCH123
- Password: school123

### Parent Login
- Parent ID: PAR456
- Password: parent123

## Future Enhancements

- Email notification system
- Mobile application
- Payment gateway integration
- Advanced reporting and analytics
- Barcode/QR code scanning for inventory management

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

- SVG illustrations created for this project
- Bootstrap 5 for the responsive layout
- Font Awesome for the icons

## Contact

For support or inquiries, please contact support@schoolinventory.example.com 
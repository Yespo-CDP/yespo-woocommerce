# Contributing

We welcome contributions to the Yespo CDP WooCommerce Plugin! Whether you're fixing bugs, improving documentation, or adding new features, your contributions are appreciated.

## 🌿 Branch Structure

This project uses **Git Flow** workflow:
- **`main`** - Production-ready code, stable releases
- **`develop`** - Active development branch, all PRs should target this branch
- **Feature branches** - Created from `develop` for new features or fixes

## 🚀 Quick Start

1. **Fork the Repository**
   ```bash
   # Fork on GitHub, then clone your fork
   git clone git@github.com:your-username/yespo-cdp.git
   cd yespo-cdp
   # Switch to development branch
   git checkout develop
   ```

2. **Set Up Development Environment**
   ```bash
   # Install PHP dependencies
   composer install
   
   # Install Node.js dependencies
   npm install
   
   # Build assets
   npm run build
   ```

3. **Configure WordPress Environment**
   - Set up a local WordPress installation
   - Install and activate WooCommerce
   - Install the plugin in development mode
   - Configure your Yespo API credentials

## 📝 Making Changes

### Branch Naming
- `feature/description` - for new features
- `fix/description` - for bug fixes
- `docs/description` - for documentation updates
- `refactor/description` - for code refactoring
- `security/description` - for security fixes

### Commit Messages
Follow conventional commits:
```
type(scope): description

Example:
feat(api): add webhook for order updates
fix(ui): resolve API key validation issue
docs(readme): update installation instructions
refactor(export): improve user data export performance
```

### Pull Request Process

1. **Create a Feature Branch from `develop`**
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/your-feature-name
   ```

2. **Make Your Changes**
   - Follow our [coding standards](#-coding-standards)
   - Update documentation if needed
   - Add tests for new functionality

3. **Validate Your Changes**
   ```bash
   npm run lint        # Check code style
   npm run build       # Ensure project builds correctly
   composer test       # Run PHP tests (if available)
   ```

4. **Submit Pull Request to `develop`**
   - Push to your fork: `git push origin feature/your-feature-name`
   - Create a Pull Request **targeting the `develop` branch** with:
     - **Clear title** describing the change
     - **Detailed description** explaining:
       - What problem does this solve?
       - What changes were made?
       - How to test the changes?
     - **Screenshots** for UI changes
     - **Link to related issues**

## 🔧 Coding Standards

### PHP & WordPress

#### File Organization
```
yespo-cdp/
├── ajax/               # AJAX handlers
├── assets/             # Frontend assets (CSS, JS, images)
├── backend/            # Admin panel functionality
│   ├── views/          # Admin templates
│   └── ActDeact.php    # Activation/deactivation hooks
├── engine/             # Core plugin engine
├── frontend/           # Frontend functionality
├── functions/          # WordPress hooks and functions
├── integrations/       # External service integrations
│   ├── esputnik/       # Yespo API integration
│   └── webtracking/    # Web tracking functionality
├── internals/          # Internal plugin components
├── languages/          # Translation files
├── rest/               # REST API endpoints
└── templates/          # Frontend templates
```

#### Code Style
- **90 characters** line length (max 120)
- **4 spaces** indentation
- Use **meaningful names** (avoid single letters except in loops)
- Follow **WordPress Coding Standards**
- Use **proper sanitization** for all user inputs

#### Naming Conventions

**Classes:**
```php
// ✅ Component classes - descriptive names with Component suffix
class Settings_Page extends Base {
    // Component logic here
}

// ✅ Service classes - descriptive names
class Yespo_Account {
    // Service logic here
}

// ✅ Integration classes - descriptive names with service prefix
class Yespo_Contact_Mapping {
    // Integration logic here
}
```

**Functions:**
```php
// ✅ WordPress hooks - descriptive names with function suffix
function yespo_check_api_authorization_function() {
    // Function logic here
}

// ✅ Helper functions - descriptive names
function yespo_get_settings() {
    // Helper logic here
}
```

**Interfaces:**
```php
// ✅ Interface naming - descriptive names with I suffix
interface Yespo_Integration_I {
    public function initialize();
}
```

#### Security Guidelines
```php
// ✅ Proper sanitization
$api_key = sanitize_text_field(wp_unslash($_POST['yespo_api_key']));

// ✅ Nonce verification
if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'action_name')) {
    return;
}

// ✅ Capability checks
if (!current_user_can('manage_options')) {
    return;
}

// ✅ Prepared statements for database queries
$wpdb->prepare("INSERT INTO %i (api_key, response, time) VALUES (%s, %s, %s)", ...);
```

### JavaScript & Frontend

#### File Organization
```
assets/
├── src/                # Source files
│   ├── styles/         # SCSS files
│   └── scripts/        # JavaScript files
└── build/              # Compiled assets
```

#### Code Style
- Use **ES6+** features
- Follow **WordPress JavaScript Coding Standards**
- Use **meaningful variable names**
- Add **error handling** for all API calls

#### Component Guidelines
```javascript
// ✅ Good: Clear component structure
class YespoAdmin {
    constructor() {
        this.apiKey = '';
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.checkAuthorization();
    }
    
    bindEvents() {
        // Event binding logic
    }
}
```

## 🐛 Bug Reports

Found a bug? Help us fix it by providing detailed information.

### Before Reporting
- Check if the issue already exists in [GitHub Issues](https://github.com/yespo/yespo-cdp/issues)
- Make sure you're using the latest version
- Try to reproduce the issue consistently
- Check WordPress and WooCommerce compatibility

### Bug Report Template
Use the GitHub bug report template when creating an issue. Include:
- WordPress version
- WooCommerce version
- PHP version
- Plugin version
- Steps to reproduce
- Expected vs actual behavior
- Error logs if applicable

## 💡 Feature Requests

Have an idea for improvement? We'd love to hear it!

### Feature Request Template
Use the GitHub feature request template when suggesting new features. Include:
- Problem statement
- Proposed solution
- Implementation ideas
- Use cases

## 🔒 Security

### Reporting Security Issues
**Do not report security vulnerabilities through public GitHub issues.**

Instead, please email us directly at: **support@yespo.io**

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### Security Guidelines for Contributors
- **Never commit** API keys, passwords, or secrets
- Use **WordPress options** for storing sensitive configuration
- **Validate all inputs** and sanitize user data
- Follow **WordPress security best practices**
- Keep dependencies up to date
- Use **nonces** for all forms and AJAX requests
- Implement **capability checks** for admin functions

## 🤝 Community Guidelines

### Code of Conduct
- **Be respectful** and inclusive
- **Focus on constructive feedback**
- **Help newcomers** feel welcome
- **Assume good intentions**
- **No harassment** or inappropriate behavior

### Getting Help
- 📖 Check the [documentation](https://yespo.io/support)
- 💬 Ask questions in GitHub Discussions
- 📧 Contact us at support@yespo.io
- 🐛 Report bugs through GitHub Issues

## 📊 Issue Management

### 🏷️ Labels We Use

| Label | Description | Used For |
|-------|-------------|----------|
| `bug` | Something isn't working | Bug reports |
| `feature` | New feature request | Feature requests |
| `enhancement` | Improvement to existing feature | Enhancements |
| `documentation` | Documentation needs update | Docs updates |
| `good first issue` | Good for newcomers | Beginner-friendly |
| `help wanted` | Extra attention needed | Community help |
| `question` | General questions | Q&A |
| `priority: critical` | Urgent fix needed | Critical bugs |
| `priority: high` | Should be fixed soon | Important issues |
| `priority: medium` | Normal priority | Standard issues |
| `priority: low` | Can wait | Minor issues |
| `status: waiting-for-feedback` | Needs more info | Pending response |
| `status: in-progress` | Being worked on | Active work |
| `scope: api` | Backend/API related | API changes |
| `scope: ui` | Frontend/UI related | UI changes |
| `scope: docs` | Documentation related | Docs changes |
| `scope: integration` | Yespo integration related | Integration changes |
| `scope: webtracking` | Web tracking related | Tracking changes |

### 📝 Issue Templates

We provide several issue templates to help you report issues effectively:

- **🐛 Bug Report** - For reporting bugs and issues
- **💡 Feature Request** - For suggesting new features
- **❓ Question** - For asking questions about the plugin

Each template includes specific sections to help us understand and address your request quickly.

## 📞 Support Channels

- **🐛 Found a bug?** → [Create a Bug Report](https://github.com/yespo/yespo-cdp/issues/new?template=bug_report.md)
- **💡 Have a feature idea?** → [Submit a Feature Request](https://github.com/yespo/yespo-cdp/issues/new?template=feature_request.md)
- **❓ Need help?** → [Ask a Question](https://github.com/yespo/yespo-cdp/issues/new?template=question.md)
- **📚 Check documentation** → [yespo.io/support](https://yespo.io/support)
- **📧 Direct support** → support@yespo.io
- **🔒 Security issues** → support@yespo.io

## 🧪 Testing

### Manual Testing Checklist
Before submitting a PR, ensure you've tested:

- [ ] Plugin activation/deactivation
- [ ] API key configuration
- [ ] User data export functionality
- [ ] Order data export functionality
- [ ] Web tracking script installation
- [ ] Event tracking (cart, purchase, etc.)
- [ ] Admin interface functionality
- [ ] Multisite compatibility (if applicable)
- [ ] WordPress/WooCommerce version compatibility

### Automated Testing
```bash
# Run PHP linting
composer lint

# Run JavaScript linting
npm run lint

# Build assets
npm run build

# Run tests (if available)
composer test
```

---

Thank you for contributing to Yespo CDP WooCommerce Plugin! 🙏

Your contributions help make Yespo better for everyone. Whether you're reporting bugs, suggesting features, or contributing code, every bit helps! 💙 
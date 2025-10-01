# OpenAI ChatBot Block for Moodle

A Moodle block plugin that integrates OpenAI's Assistant API to provide intelligent tutoring and course-specific assistance.

## 🚀 Features

- **OpenAI Assistant Integration**: Uses OpenAI's latest Assistant API v2
- **Course Context Awareness**: Provides responses tailored to specific course content
- **Modern JavaScript**: ES6 modules with proper AMD integration
- **External Web Services**: AJAX calls using Moodle's external service API
- **Template System**: Modern Mustache templates with Output API
- **Security**: Proper input sanitization and session key validation
- **Multilingual**: Support for English and Spanish languages

## 📋 Requirements

- Moodle 4.1 or later
- Valid OpenAI API key
- Configured OpenAI Assistant
- Internet connection for API calls

## 🔧 Installation

1. Download the plugin
2. Extract to `moodle/blocks/block_openai_chatbot/`
3. Visit Site Administration → Notifications to install
4. Configure API settings (see Configuration below)

## ⚙️ Configuration

Navigate to **Site Administration → Plugins → Blocks → OpenAI ChatBot**:

1. **OpenAI API Key**: Enter your API key from https://platform.openai.com/
2. **Assistant ID**: ID of your OpenAI Assistant (create at https://platform.openai.com/assistants)
3. **Bot Name**: Display name for the chatbot (default: "AI Tutor")
4. **Course Context**: Enable contextual responses based on course content
5. **Max Response Time**: Maximum wait time for API responses (default: 30 seconds)

## 📖 Usage

1. Add the **OpenAI ChatBot** block to any course
2. Students and teachers can type questions in the interface
3. The AI provides contextual responses using course information
4. All interactions are processed through secure web services

## 🏗️ Technical Architecture

- **Modern JavaScript**: ES6 modules using Moodle's AMD loader
- **Web Services**: External API for AJAX communication
- **Templates**: Mustache templates with Output API
- **Security**: Input validation, session keys, capability checks
- **HTTP Client**: Moodle's cURL wrapper instead of direct cURL calls

## 🔐 Security Features

- Session key validation for all form submissions
- Parameter sanitization using Moodle's PARAM_* constants
- Capability-based access control
- Context validation for all web service calls
- XSS protection through proper output escaping

## 🌐 Supported Languages

- English (en)
- Spanish (es)

## 📝 Version History

- **v1.0.0** (2025-10-01): Initial release with modern Moodle standards
  - OpenAI Assistant API v2 integration
  - ES6 JavaScript modules
  - External web services
  - Template system implementation
  - Security improvements

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Follow Moodle coding standards
4. Submit a pull request

## 📄 License

Licensed under the GNU GPL v3 or later.

## 👨‍💻 Author

**Esteban Piazza**  
Email: esteban@codeki.org

## 🐛 Support

For issues and questions:
1. Check the [Issues](https://github.com/estebanpiazza/block_openai_chatbot/issues) page
2. Create a new issue with detailed information
3. Include Moodle version, PHP version, and error logs

## 🔗 Links

- [OpenAI Platform](https://platform.openai.com/)
- [Moodle Developer Documentation](https://docs.moodle.org/dev/)
- [Plugin Directory](https://moodle.org/plugins/)
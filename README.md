# BG Remover Pro

A professional AI-powered web application for background removal and image upscaling built with Laravel, Python, and modern web technologies.

## 🚀 Features

### **Background Removal**
- AI-powered background removal using rembg
- Support for multiple input formats (PNG, JPG, GIF, WebP)
- PNG output with transparent background
- JPEG output with customizable background colors
- Automatic color palette generation from uploaded images
- Real-time progress tracking

### **Image Upscaling**
- Upscale images up to 4x using Real-ESRGAN
- High-quality AI upscaling technology
- Support for 2x and 4x scaling factors
- Background removal + upscaling workflow
- Queue-based processing for large files

### **User Experience**
- Modern, responsive UI with Tailwind CSS
- Drag and drop file uploads
- Real-time progress tracking
- Color palette generation for JPEG backgrounds
- Seamless workflow between background removal and upscaling

### **Technical Features**
- Laravel backend with queue processing
- Python AI services (rembg, Real-ESRGAN)
- Real-time progress updates
- File validation and error handling
- Responsive design for all devices

## 🛠️ Technology Stack

### **Backend**
- **Laravel 10** - PHP framework for web application
- **Python 3** - AI processing services
- **MySQL/PostgreSQL** - Database
- **Redis** - Queue management

### **AI Services**
- **rembg** - Background removal
- **Real-ESRGAN** - Image upscaling
- **Pillow** - Image processing

### **Frontend**
- **Tailwind CSS** - Styling
- **Alpine.js** - Interactive components
- **Laravel Blade** - Templates

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.1+** with extensions: `curl`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `zip`
- **Composer** - PHP package manager
- **Python 3.8+** with pip
- **Node.js 16+** and npm
- **MySQL/PostgreSQL** database
- **Redis** (optional, for queue processing)

## 🚀 Quick Start

### **1. Clone the Repository**
```bash
git clone https://github.com/pplcallmesatz/PixelWizard.git
cd PixelWizard
```

### **2. Install Dependencies**

#### **PHP Dependencies**
```bash
composer install
```

#### **Python Virtual Environment (Recommended)**
It is recommended to use a Python virtual environment (`venv`) to isolate dependencies:
```bash
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate.bat
```

#### **Python Dependencies**
```bash
pip install -r requirements.txt
```

#### **Node.js Dependencies**
```bash
npm install
```

### **3. Environment Setup**

#### **Copy Environment File**
```bash
cp .env.example .env
```

#### **Generate Application Key**
```bash
php artisan key:generate
```

#### **Configure Database**
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bgremover
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### **Run Migrations**
```bash
php artisan migrate
```

### **4. Start All Services**

#### **One Command Start (Recommended)**
```bash
./start-server.sh
```

This single command starts:
- Laravel development server (Port 8000)
- Python backend server (Port 5000)
- Laravel queue worker
- NPM development server (asset compilation)

#### **Manual Start (Alternative)**
```bash
# Terminal 1: Laravel Server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Python Backend
python3 backend/app.py

# Terminal 3: Queue Worker
php artisan queue:work --memory=1024 --timeout=300

# Terminal 4: NPM Dev (Asset Compilation)
npm run dev
```

### **5. Access the Application**
- **Main Application**: http://localhost:8000
- **Background Remover**: http://localhost:8000/bgremove
- **Image Upscaler**: http://localhost:8000/upscale

## 🎯 Server Management

### **Start All Services**
```bash
./start-server.sh
```

### **Stop All Services**
```bash
./stop-server.sh
```

### **View Logs and Status**
```bash
./view-logs.sh
```

### **Individual Service Commands**

#### **Laravel Server**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

#### **Python Backend**
```bash
python3 backend/app.py
```

#### **Queue Worker**
```bash
php artisan queue:work --memory=1024 --timeout=300
```

#### **Asset Compilation**
```bash
npm run dev
```

## 📁 Project Structure

```
bgremover/
├── app/
│   ├── Http/Controllers/
│   │   ├── BackgroundRemovalController.php
│   │   ├── ImagePaletteController.php
│   │   └── UpscaleController.php
│   └── Jobs/
│       └── ProcessUpscaleJob.php
├── backend/
│   ├── app.py
│   ├── requirements.txt
│   └── models/
├── resources/
│   ├── views/
│   │   ├── bgremove/
│   │   ├── upscale/
│   │   └── landing.blade.php
│   └── css/
├── routes/
│   └── web.php
├── database/
│   └── migrations/
├── storage/
│   └── app/
│       ├── uploads/
│       └── processed/
└── scripts/
    ├── start-server.sh
    ├── stop-server.sh
    └── view-logs.sh
```

## 🔧 Configuration

### **Environment Variables**

#### **Database Configuration**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bgremover
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### **File Storage**
```env
FILESYSTEM_DISK=local
```

#### **Queue Configuration**
```env
QUEUE_CONNECTION=database
```

### **Python Backend Configuration**

The Python backend runs on port 5000 by default. You can modify this in `backend/app.py`:

```python
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
```

## 🎨 Usage Guide

### **Background Removal**

1. **Navigate to Background Remover**
   - Go to http://localhost:8000/bgremove

2. **Upload Image**
   - Click "Choose File" or drag and drop an image
   - Supported formats: PNG, JPG, GIF, WebP (up to 8MB)

3. **Choose Output Format**
   - **PNG**: Transparent background (recommended)
   - **JPEG**: Colored background with custom color picker

4. **Process Image**
   - Click "Remove Background"
   - Watch real-time progress
   - Download the result

### **Image Upscaling**

1. **Navigate to Upscaler**
   - Go to http://localhost:8000/upscale
   - Or click "Upscale" from background removal results

2. **Upload Image**
   - Select an image to upscale
   - Or use a processed image from background removal

3. **Choose Scale Factor**
   - **2x**: Double the resolution
   - **4x**: Quadruple the resolution

4. **Process Image**
   - Click "Upscale Image"
   - Monitor progress in real-time
   - Download the upscaled result

## 🔍 Troubleshooting

### **Common Issues**

#### **Port Already in Use**
```bash
# Check what's using the port
lsof -i :8000
lsof -i :5000

# Kill the process
kill -9 <PID>
```

#### **Queue Worker Issues**
```bash
# Clear failed jobs
php artisan queue:flush

# Restart queue worker
php artisan queue:restart
```

#### **Python Dependencies**
```bash
# Reinstall Python dependencies
pip install -r requirements.txt --force-reinstall
```

#### **Asset Compilation Issues**
```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

### **Logs and Debugging**

#### **View Laravel Logs**
```bash
tail -f storage/logs/laravel.log
```

#### **View Queue Logs**
```bash
tail -f storage/logs/queue.log
```

#### **View Python Backend Logs**
```bash
tail -f backend/app.log
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Developer

**Satheesh Kumar S**

A passionate full-stack developer and UI/UX designer with expertise in modern web technologies and AI integration.

### **About Me**
I specialize in creating innovative web applications that combine cutting-edge AI technologies with intuitive user experiences. This BG Remover Pro project showcases my skills in Laravel development, Python AI integration, and modern frontend design.

### **Skills & Expertise**
- **Frontend**: UI/UX Design, HTML/CSS, JavaScript, Tailwind CSS, Alpine.js
- **Backend**: Laravel, PHP, Python, API Development
- **AI/ML**: Image Processing, Background Removal, Real-ESRGAN, rembg
- **DevOps**: Automation, CI/CD, Server Management
- **Open Source**: Active contributor to various projects

### **Contact Information**
- **Email**: satheeshssk@icloud.com
- **Phone**: +91 8754999592
- **LinkedIn**: [pplcallmesatz](https://www.linkedin.com/in/pplcallmesatz/)
- **Instagram**: [pplcallmesatz](https://www.instagram.com/pplcallmesatz/)
- **Medium**: [pplcallmesatz](https://medium.com/@pplcallmesatz)
- **GitHub**: [pplcallmesatz](https://github.com/pplcallmesatz)

### **Portfolio & Projects**
I create new and innovative solutions in UI/UX, HTML/CSS, Laravel, Automation, AI-powered development tools, and contribute to open-source projects. Each project is crafted with attention to detail and user experience.

## ☕ Support

If you find this tool useful and would like to support my work, consider buying me a coffee! Your support helps me continue developing innovative solutions and contributing to the open-source community.

[![Buy Me a Coffee](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/satheeshdesign)

**Your support means a lot and helps me:**
- 🚀 Continue developing innovative tools
- 📚 Create more educational content
- 🌟 Contribute to open-source projects
- 💡 Build new AI-powered applications
- 🎨 Improve user experiences

*Thank you for your support! 🙏*

## 🙏 Acknowledgments

- **rembg** - Background removal library
- **Real-ESRGAN** - Image upscaling technology
- **Laravel** - PHP framework
- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - Lightweight JavaScript framework

---

**Made with ❤️ by Satheesh Kumar S**

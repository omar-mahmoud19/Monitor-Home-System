# Monitor Home System

A smart full-stack web application for monitoring and managing home resources, appliances, automation, and energy usage in real time.

The system provides dashboards, reports, goals tracking, automation controls, and user management features designed to simulate a modern smart home environment.

---

## Authors

Developed by:

* Omar Mahmoud
* Tatek Mohamed
* Omar Shaltout

## Features

* Smart Home Dashboard
* Appliance Monitoring & Control
* Automation System
* Energy & Resource Usage Tracking
* Reports & Analytics
* Goals Management
* User Authentication & Session Management
* Settings Management
* MySQL Database Integration
* REST-style PHP APIs

---

## Technologies Used

### Backend

* PHP
* MySQL
* REST APIs
* Session Authentication

### Frontend

* HTML5
* CSS3
* JavaScript

### Database

* MySQL (`smarthome.sql`)

---

## Project Structure

```bash
AOT/
│
├── api/                # Backend APIs
├── config/             # Configuration files
├── data/               # Local JSON data
├── models/             # System models
├── views/              # Frontend pages
├── dashboard.php
├── appliances.php
├── automation.php
├── goals.php
├── reports.php
├── settings.php
├── login.php
└── smarthome.sql
```

---

## Installation & Setup

### 1. Clone Repository

```bash
git clone https://github.com/omar-mahmoud19/Monitor-Home-System.git
```

### 2. Move Project to XAMPP / htdocs

```bash
C:/xampp/htdocs/
```

### 3. Import Database

* Open **phpMyAdmin**
* Create a database named:

```sql
smarthome
```

* Import the file:

```bash
smarthome.sql
```

### 4. Configure Database Connection

Edit:

```bash
config/config.php
```

Update your database credentials.

### 5. Run the Project

Start:

* Apache
* MySQL

Then open:

```bash
http://localhost/AOT/
```

---

## Main Modules

### Dashboard

Provides real-time monitoring for devices and system statistics.

### Appliances

Manage and monitor connected appliances.

### Automation

Create automated smart home actions and workflows.

### Goals

Track energy-saving and smart usage goals.

### Reports

Generate reports and analytics for home usage.

### Settings

Manage user and system preferences.

---

## API Endpoints

Example APIs:

```bash
/api/dashboard.php
/api/appliances.php
/api/automation.php
/api/reports.php
/api/settings.php
```

---

## Screenshots

### Login Page
![Login](Documentation%20and%20Diagrams/login.png)

---

### Dashboard
![Dashboard](Documentation%20and%20Diagrams/dashboard.png)

![Dashboard 2](Documentation%20and%20Diagrams/dashboard2.png)

---

### Appliances Management
![Appliances](Documentation%20and%20Diagrams/appliances.png)

![Appliances 2](Documentation%20and%20Diagrams/appliances2.png)

---

### Automation System
![Automation](Documentation%20and%20Diagrams/automation.png)

---

### Goals Tracking
![Goals](Documentation%20and%20Diagrams/goals.png)

---

### Reports
![Reports](Documentation%20and%20Diagrams/reports.png)

---

### Settings
![Settings](Documentation%20and%20Diagrams/settings.png)

![Settings 2](Documentation%20and%20Diagrams/settings2.png)

---

## Future Improvements

* Real IoT Integration
* Mobile Responsive UI Enhancements
* Push Notifications
* AI-based Automation Suggestions
* Live Device Synchronization



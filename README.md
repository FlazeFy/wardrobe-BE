# 👕 WARDROBE

> **Wardrobe Web** is a **web-based clothing management** application that helps users organize their **daily wardrobe**, **automate outfit selection**, and **schedule laundry routines**. Users can input **detailed data** for each clothing item such as name, type, brand, material, size, and price and track when each piece is worn. The system also allows users to set reminders, enabling **automatic notifications** when an outfit is scheduled to be used the next day. The platform integrates with **Wardrobe Mobile**, **Wardrobe Telegram Bot**, **Wardrobe Line Bot**, and **Wardrobe Desktop**, offering a cross-platform experience through a **single account**. Users can manage and view their wardrobe, schedules, and laundry history across devices. Data export features in **PDF** and **Excel** formats are also available for greater flexibility in wardrobe management. While the application is accessible on both desktop and mobile browsers, administrative access is reserved for the **Wardrobe Desktop Admin** on desktop devices.

> This repository is the **primary backend codebase** for the Wardrobe app, built using **PHP** with the **Laravel framework**. There is also next version of wardrobe apps that built using **Golang** with the **Gin framework** and can be accessed at:
https://github.com/FlazeFy/wardrobe-Go

## 📋 Basic Information

If you want to see the project detail documentation, you can read my software documentation document. 

1. **Pitch Deck**
https://docs.google.com/presentation/d/131-Cc_QHO8SPYvb6dBx_EaEpwBAz6kG5YgyHUebXjjk/edit?usp=drive_link 

2. **Diagrams**
https://drive.google.com/drive/folders/16Mo0zd2CKzJRJuLGYjbHlbgfUJ7z13mK?usp=drive_link

3. **Software Requirement Specification**
https://docs.google.com/document/d/1GPFVqSjwaWZ_O7wjb8oS_voJBDJkwS95eEyniI2ydrc/edit?usp=drive_link 

4. **User Manual**
https://docs.google.com/presentation/d/1qiH8gPTqc3haEH5Y6UGtngXQtlJ39kroGciOyHh7XiI/edit?usp=drive_link

5. **Test Cases**
https://docs.google.com/spreadsheets/d/1LoqYYub1JHJyIHsbL89LDQijj_-Hv74QZUgRrwtXMhI/edit?usp=sharing

### 🌐 Deployment URL

Backend (Swagger Docs) : ...

### 📱 Demo video on the actual device

[URL]

---

## 🎯 Product Overview
- **Clothing & Outfit Data Management**
Users can manage their clothing data within the Wardrobe application. The data recorded may include clothing name, type, brand, price, material, and size.

- **Scheduling & History**
From the saved clothing data, users can log each time a clothing item or outfit is used, and set reminder schedules. The system will then notify users whenever an outfit is scheduled to be used the next day.

- **Laundry Management**
Users can also track the laundry process of clothes after being worn, up to the status where the clothing is ready to be used again.

- **Automatic Outfit Suggestion**
Based on the clothing data, the system can generate various outfit combinations and recommend an outfit for the next day. This is determined by usage history, availability status, weather conditions, and user preferences.

- **Data Export**
The application provides convenience for users who want to view complete datasets of clothing, usage history, or laundry history. Export to PDF and Excel formats is available to support more customized wardrobe management.

## 🚀 Target Users

1. **Everyday People**
Individuals who want to efficiently manage their daily clothing collections, track outfit usage, schedule laundry, and receive outfit suggestions.

2. **Laundry Service Customers**
Users who frequently use laundry services and need to monitor the washing and return status of their clothes.

## 🧠 Problem to Solve

1. People often **forget what clothes they own**, where they are, or when they last wore or washed them leading to **inefficient usage** and cluttered wardrobes.
2. Planning daily outfits manually is **time-consuming** and can lead to repeated usage, poor combinations, or unprepared clothing.
3. Users **forget to wash** or **retrieve laundry** in time, leaving them without clean clothes when needed.
4.  **Manually recording** outfit usage, laundry status, or purchase details becomes overwhelming.
5. **Exporting wardrobe data** for budgeting, or personal reference is **difficult** without a structured digital system.

## 💡 Solution

1. Offer a feature for users to digitally log and **manage clothing** items with details like name, size, brand, material, price, and category—making wardrobes trackable and searchable.
2. **Automatically generate outfit** suggestions based on clean clothes, usage history, and weather to simplify daily planning.
3. Let users **schedule outfit** usage and receive **laundry reminders** to ensure clothes are clean and ready in time.
4. **Sync data across devices** (Web, Mobile, Telegram, and Line Bot) so users can update or access wardrobe records anytime, anywhere.
5. Provide data export tools to **download usage history**, **laundry logs**, and **clothing details** in **Excel** or **PDF** format for easy reference or sharing.

## 🔗 Features

- ✍️ Clothing & Outfit Data Management
- ⏰ Scheduling & History
- 👕 Laundry Management
- 👗 Automatic Outfit Suggestion
- 🤖 Telegram and Line Bot Chat Integration
- 📄 Data Export

---

## 🛠️ Tech Stack

### Backend

- PHP Laravel
- PHP - Telegram Bot
- PHP - Line Bot

### Database

- MySQL

### Others Data Storage

- Firebase Storage (Cloud Storage for Asset File)

### Infrastructure & Deployment

- Cpanel (Deployment)
- Github (Code Repository)
- Firebase (External Services)

### Other Tools & APIs

- Postman
- Swagger Docs

---

## 🏗️ Architecture
### Structure

### 📁 Project Structure

| Directory/File       | Purpose                                                                                   |
|----------------------|-------------------------------------------------------------------------------------------|
| `app/Exceptions/`    | Custom exception handling logic.                                                          |
| `app/Exports/`       | Data export logic, e.g., for Excel or PDF generation.                                     |
| `app/Helpers/`       | Utility / helper functions used across the app.                                             |
| `app/Http/Controllers/` | Handles incoming HTTP requests and sends responses.                                   |
| `app/Jobs/`          | Queued jobs for background processing.                                                    |
| `app/Mail/`          | Configuration or instance of email broadcast.                                                    |
| `app/Models/`        | Eloquent model definitions mapped to database tables.                                     |
| `app/Providers/`     | Service providers for bootstrapping application services.                                 |
| `app/Rules/`         | Custom form request validation rules like allowed value.                                                     |
| `app/Schedule/`      | Scheduled tasks like cron jobs using Laravel scheduler.                                   |
| `config/`            | Configuration files for services, database, cache, auth, and constants.                   |
| `database/factories/`| Define what kind of data for dummy.                                                 |
| `database/migrations/`| Defines database template.                                                         |
| `database/seeders/`  | Seeds database with default or dummy data.                                              |
| `firebase/`          | Service account JSON.                                                    |
| `public/`            | Publicly accessible folder, serves as the document root for web servers.                  |
| `resources/`         | Views, language files, and other frontend resources.                                      |
| `routes/`            | API routes / endpoints. |
| `storage/`           | File uploads. |
| `tests/`             | Feature and unit tests.                                                                   |
| `tests_reports/`     | Test report outputs.                                           |
| `vendor/`            | Composer-managed PHP dependencies.                                                        |
| `.env`               | Environment-specific variables.                                                           |
| `.env.example`       | Example environment configuration file.                                                   |
| `.gitignore`         | Specifies files and folders to be ignored by Git.                                         |                                             |


---

### 🧾 Environment Variables

To set up the environment variables, just create the `.env` file in the root level directory.

| Variable Name                        | Description                                                              |
|----------------------------------|--------------------------------------------------------------------------|
| `DB_CONNECTION`                  | Database driver/connection (e.g., `mysql`, `pgsql`)                      |
| `DB_HOST`                        | Database host (e.g., `localhost`)                                        |
| `DB_PORT`                        | Database port (e.g., `3306`)                                             |
| `DB_USER`                        | Database username                                                        |
| `DB_PASSWORD`                    | Database password                                                        |
| `DB_DATABASE`                    | Name of the primary database                                             |
| `TEST_DB_HOST`                   | Host for the test database                                               |
| `TEST_DB_PORT`                   | Port for the test database                                               |
| `TEST_DB_USER`                   | Username for the test database                                           |
| `TEST_DB_PASSWORD`               | Password for the test database                                           |
| `TEST_DB_NAME`                   | Name of the test database                                                |
| `FIREBASE_BUCKET_NAME`           | Firebase Storage bucket name for handling file uploads                   |
| `GOOGLE_APPLICATION_CREDENTIALS`| Path to Firebase service account JSON file                               |
| `TELEGRAM_BOT_TOKEN`             | Telegram bot token for chat integration                                  |
| `LINE_BOT_TOKEN`                 | Line bot token for chat integration                                      |
| `MAIL_MAILER`                    | Mail transport method (e.g., `smtp`)                                     |
| `MAIL_HOST`                      | Mail server host (e.g., `smtp.mailtrap.io`)                              |
| `MAIL_PORT`                      | Mail server port (e.g., `587`)                                           |
| `MAIL_USERNAME`                  | Mail server username                                                     |
| `MAIL_PASSWORD`                  | Mail server password                                                     |
| `MAIL_FROM_ADDRESS`              | Default email address to send from                                       |
| `MAIL_ENCRYPTION`                | Encryption protocol (e.g., `tls`, `ssl`)                                 |
| `MAIL_FROM_NAME`                 | Name that appears in sent emails                                         |
| `OPEN_WEATHER_API_KEY`          | API key for fetching weather data from OpenWeather                      |
| `PORT`                           | Port on which the application will run (e.g., `9000`)                    |


---

## 🗓️ Development Process

### Technical Challenges

- **Daily Limitation** for data transaction in Firebase Storage
- Not all **helpers** can be tested in **automation testing**
- Feature that return the **output in Telegram / Line Chat or Exported File** must be **tested manually** 

---

## 🚀 Setup & Installation

### Prerequisites

#### 🔧 General
- Git installed
- A GitHub account
- Basic knowledge of PHP, Software Testing, Firebase Service, and SQL Databases
- Code Editor
- Telegram Account
- Line Account
- Postman

#### 🧠 Backend
- PHP version 8.1 or higher
- Composer version 2.8 or higher
- Git for cloning the repository.
- MySQL database.
- Make (optional), if your project includes a Makefile to simplify common commands.
- Firebase service account JSON file or Google App Credential.
- Telegram Bot token, you can get it from **Bot Father** `@BotFather`
- Telegram User ID for testing the scheduler chat in your Telegram Account. You can get it from **IDBot** `@username_to_id_bot`
- Line Bot token, you can get it from **Line Developer Console** 
- Line User ID for testing the scheduler chat in your Line Account. You can get it from webhook events
- Internet access from the hosting server (for Telegram webhook polling or long-polling)

### Installation Steps

**Local Init & Run**
1. Download this Codebase as ZIP or Clone to your Git
2. Set Up Environment Variables `.env` at the root level directory. You can see all the variable name to prepare at the **Project Structure** before or `.env.example`
3. Install Dependencies using `composer install`
4. **Database Migration** will run if you execute the command `php artisan migrate`.
5. **Seeders** will run if you execute `php artisan db:seed`.
6. **Task Scheduler** in Laravel is managed via `php artisan schedule:run`, usually triggered by a system cron job.
7. **Queue** for background process will run after you execute `php artisan queue:work`.
8. **Run the Laravel** using `php artisan serve`

**CPanel Deployment**
1. Source code uploaded to CPanel
2. Prepare the `.htaccess` in root directory
3. ...

---

## 👥 Team Information

| Role     | Name                    | GitHub                                     | Responsibility |
| -------- | ----------------------- | ------------------------------------------ | -------------- |
| Backend Developer  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Backend and Telegram Bot Codebase         |
| Frontend Developer  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Frontend Codebase         |
| Mobile Developer  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Mobile Codebase         |
| System Analyst  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Diagram & Software Docs         |
| Quality Assurance  | Leonardho R. Sitanggang | [@FlazeFy](https://github.com/FlazeFy)     | Manage Testing & Documented The API         |

---

## 📝 Notes & Limitations

### ⚠️ Precautions When Using the Service
- Ensure API endpoints requiring authentication are protected with proper middleware.
- Do not expose sensitive environment variables (e.g., API keys, database credentials) in public repositories.
- Avoid using seeded dummy data in production environments.
- Avoid using seeded dummy data with large seed at the same time.

### 🧱 Technical Limitations
- Telegram & Line bot polling may cause delays or downtime if the server experiences high load

### 🐞 Known Issues
- Limitation when using Firebase Storage for free plan Firebase Service, upgrade to Blaze Plan to use more.

---

## 🏆 Appeal Points

- ✅ **Smart Wardrobe Management**: Easily organize and track clothing items with full details including size, material, category, price, and brand.
- 🤖 **Bot Interaction**: Integrated with Telegram and Line Bot to send real-time reminders for outfit schedules and laundry tasks.
- 🧪 **Test-Driven Development**: Built with a focus on E2E, integration, and unit tests for stable and reliable API behavior.
- 📄 **Exportable Reports**: Generate and download clothing usage and laundry history in PDF or Excel formats.
- 💡 **Real-Life Use Case**: Solves a modern lifestyle issue of managing outfits efficiently with automation and reminders.
- ⭐️ **Clean & Modular Codebase**: Designed using Go (Gin) with separation of concerns, making the backend easy to scale and maintain.

---

_Made with ❤️ by Leonardho R. Sitanggang_
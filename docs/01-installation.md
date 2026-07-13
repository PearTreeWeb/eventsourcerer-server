# Installation Guide

Welcome to the installation guide for Event Sourcerer. Follow these steps to get started:

## Requirements

- **PHP** 8.3 or higher
- Composer
- Node.js for asset building

## Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   ```

2. **Navigate into the directory:**
   ```bash
   cd event-sourcerer
   ```

3. **Install PHP dependencies:**
   ```bash
   composer install
   ```

4. **Install JavaScript dependencies:**
   ```bash
   npm install
   ```

5. **Compile assets:**
   ```bash
   npm run build
   ```

6. **Setup the database:**
   Configure your `.env` file and run:
   ```bash
   bin/console doctrine:migrations:migrate
   ```

7. **Run the application:**
   ```bash
   symfony server:start
   ```

The application should now be running locally. Access it through your web browser at `http://localhost:8000`.

Refer to the [Quick Start](02-quick-start.md) guide to explore more about using the system and its features.


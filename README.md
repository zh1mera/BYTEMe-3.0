# BYTEme

BYTEme is an educational programming app designed to help students grasp the fundamentals of coding in a structured and engaging way. Through level-based quizzes and interactive challenges, BYTEme guides learners from basic concepts to more advanced topics in Python, Java, and JavaScript. Whether you're just starting out or looking to reinforce your understanding, BYTEme provides a step-by-step path to build confidence and competence in programming. With support for both web and desktop platforms, it's a flexible tool for learning anywhere, anytime.

## Features

- 📘 Learn Python, Java, and JavaScript basics  
- 🧠 Progress through beginner to pro levels  
- 💾 Track progress locally  
- 💻 Web and desktop support

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP, MySQL  
- **Desktop Version:** Electron.js  

## Installation

### Run on Web

1. Clone or download the project:
   ```bash
   git clone https://github.com/yourusername/byteme.git
2. Set up a local server (e.g., XAMPP or MAMP).

3. Place the project folder inside the htdocs directory.

4. Import the provided MySQL database into phpMyAdmin.

5. Start Apache and MySQL.

6. Access the app in your browser:
   ```arduino
   http://localhost/byteme/welcome.php

### Run on Desktop (Electron)
1. Clone the project and navigate to the folder:
   ```bash
   git clone https://github.com/yourusername/byteme.git
   cd byteme

2. Make sure Node.js is installed.
   Install Electron:
   ``bash
   npm install electron --save-dev

3. Run the desktop app:
   ```bash
   npx electron .

## Make sure your PHP server is running if your Electron app depends on backend interaction.

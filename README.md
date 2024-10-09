# Customer Relationship Management (CRM) and Resources Management Web Application

## Project Overview
This project presents a CRM web application aimed at streamlining customer relationship management and resource planning for businesses. The application is designed to deliver a personalized experience for both the organization and its clients, and integrates features that are essential for a business, from secure user authentication to service order management and customer support.

## Features
- **Customer Management**: 
  - Secure authentication and role-based access control
  - Email verification and password recovery
- **Product & Service Management**:
  - Product catalog with search functionality
  - Service order processing and tracking
- **Support System**:
  - Ticket submission and tracking for customer support
  - Administrator dashboard to manage user roles, products, and services
- **Online Payments**:
  - Secure payment processing integrated with Stripe
- **Administrative Dashboard**:
  - Comprehensive dashboard for administrators to manage orders, products, users, and roles

## Tech Stack
- **Backend**: Laravel with Jetstream and Orchid Software for secure and robust application development
- **Frontend**: Laravel Blade and Livewire components for dynamic user interfaces
- **Database**: SQLite for data storage, providing a lightweight and easy-to-maintain database solution
- **Payments**: Stripe for secure online payment processing
- **Email Service**: Mailgun for email verification and notifications

![Structural difference between Laravel and Orchid](https://github.com/user-attachments/assets/cd62b52d-1611-4a53-a779-a02580024cf2)

## Architecture
This CRM application utilizes the **Model-View-Controller (MVC)** architecture, ensuring clear separation of concerns and scalability:
- **Model**: Manages the data logic and interacts with the database
- **View**: Presents data to the user and handles UI elements
- **Controller**: Coordinates the data flow between the Model and View, managing user requests

![MVC Figure](https://github.com/user-attachments/assets/5c346f01-7364-414a-ae93-cfce33c269c0)


## Use Cases
- **Customer Management**: Businesses can manage their clients, track orders, and support requests effectively.
- **Order and Service Management**: Workshop teams can monitor and update the status of orders, from creation to fulfillment.
- **Support Ticket System**: Customers can submit support tickets, which the staff can manage via an integrated ticketing dashboard.
- **Secure Online Payments**: Businesses can process payments securely for products and services.

## Demo
A live demo of the application can be viewed on [YouTube](https://www.youtube.com/watch?v=2tX74lfBLR0).

## Conclusion
This CRM system demonstrates a cost-effective solution for small to medium-sized businesses seeking to digitalize their operations. By integrating essential CRM functionalities with secure online payments and role-based access control, the application ensures a streamlined experience for users and administrators alike.

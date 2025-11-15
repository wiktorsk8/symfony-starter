# URL Shortener API

A modern URL shortening service built with Symfony, utilizing Docker and FrankenPHP for optimal performance.

## Architecture

This project implements a **CQRS (Command Query Responsibility Segregation)** pattern within a **modular monolithic** architecture, following **Domain-Driven Design (DDD)** principles.

### Key Architectural Features

- **CQRS Pattern**: Separation of read and write operations for better scalability and maintainability
- **Modular Monolith**: Organized into distinct, loosely-coupled modules
- **Domain-Driven Design**: Business logic organized around domain concepts
- **Docker & FrankenPHP**: Modern containerized deployment with high-performance PHP runtime

## Modules

### User Module

Handles user management and authentication:

- User registration and management
- JWT (JSON Web Token) based authentication
- Secure token-based authorization

### ShortLink Module

Core URL shortening functionality:

- URL shortening with custom or auto-generated codes
- Click/access limit configuration for shortened links
- Link analytics and access tracking
- Expiration and limit enforcement

## Technology Stack

- **Framework**: Symfony
- **Runtime**: FrankenPHP
- **Containerization**: Docker
- **Authentication**: JWT tokens
- **Architecture**: CQRS + DDD

## Features

- ✅ Secure JWT-based authentication
- ✅ URL shortening with custom aliases
- ✅ Configurable access limits per shortened link
- ✅ Modular, maintainable codebase
- ✅ CQRS for optimal read/write performance
- ✅ Docker-based deployment

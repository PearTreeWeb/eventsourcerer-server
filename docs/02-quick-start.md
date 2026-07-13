# Quick Start Guide

This guide will help you get up and running with Event Sourcerer quickly. After completing the [Installation Guide](01-installation.md), follow these steps to start using the platform.

## First Steps

### 1. Create Your First User Account

1. Navigate to your Event Sourcerer installation (typically `http://localhost:8000`)
2. Click on the user registration link
3. Fill in your details to create an administrator account
4. Complete the email verification process if required

### 2. Access the Dashboard

After logging in, you'll be taken to the main dashboard where you can see:
- **Recent Connections**: Active socket connections to your server
- **Recent Streams**: Latest event streams in your system
- **Widgets**: Custom dashboard components for data visualization

### 3. Start the Socket Server

For real-time event streaming, start the socket server:

```bash
bin/console app:socket-server:start
```

### 4. Create Your First Application

Applications represent the systems that will send events to Event Sourcerer:

1. Navigate to **Applications** in the main menu
2. Click **Register New Application**
3. Provide:
   - **Application Name**: A unique identifier for your app
   - **Description**: What this application does
4. Save your application

### 5. Define Your First Event

Events are the building blocks of your event-sourced system:

1. Go to **Events** in the main menu
2. Click **Register New Event**
3. Configure:
   - **Event Name**: Descriptive name (e.g., "UserRegistered")
   - **Properties**: Define the data structure for this event
   - **Property Types**: Set appropriate data types (Text, Integer, UUID, etc.)
4. Save your event definition

### 6. Set Up a Projection

Projections create read models from your event streams:

1. Navigate to **Projections**
2. Click **Register New Projection**
3. Define:
   - **Projection Name**: What state this projection represents
   - **Properties**: The fields in your read model
   - **Mutations**: How events modify the projection state
4. Save and activate your projection

## What's Next?

Now that you have the basics set up:

- **Monitor Activity**: Check the dashboard for real-time updates
- **View Streams**: See your events as they flow through the system
- **Create Widgets**: Build custom dashboard components
- **Manage Users**: Add team members and set permissions

## Common First-Time Tasks

### Testing Event Flow

1. Use the API or socket connection to send a test event
2. Verify it appears in the **Streams** section
3. Check that your projections update correctly
4. Monitor the dashboard for connection activity

### Security Setup

1. Create additional user accounts for your team
2. Set appropriate user roles and permissions
3. Configure SSL certificates for production use
4. Set up proper database security

## Getting Help

- Check the [Dashboard Overview](06-dashboard.md) for interface details
- Read [Event Management](09-events.md) for advanced event configuration
- See [Application Management](08-applications.md) for application setup details
- Visit [Troubleshooting](17-troubleshooting.md) if you encounter issues

Ready to dive deeper? Explore the [Event Sourcing Basics](04-event-sourcing-basics.md) to understand the concepts behind Event Sourcerer.

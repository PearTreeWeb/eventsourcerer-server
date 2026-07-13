# Event Sourcing Basics

Understanding event sourcing is crucial to effectively using Event Sourcerer. This guide explains the fundamental concepts and how they apply to your system.

## What is Event Sourcing?

Event sourcing is a pattern where application state is stored as a sequence of events. Instead of storing current state directly, you store the events that led to that state. This approach offers several advantages:

- **Complete Audit Trail**: Every change is recorded
- **Temporal Queries**: View state at any point in time
- **Debugging**: Replay events to understand system behavior
- **Flexibility**: Create new views of data without migration

## Core Concepts

### Events
Events are immutable records of something that happened in your system. They contain:
- **Event Type**: What happened (e.g., "UserRegistered", "OrderPlaced")
- **Event Data**: The details of what happened
- **Timestamp**: When it occurred
- **Metadata**: Additional context (user ID, IP address, etc.)

**Example Event:**
```json
{
  "eventType": "UserRegistered",
  "eventId": "550e8400-e29b-41d4-a716-446655440000",
  "timestamp": "2025-01-15T10:30:00Z",
  "data": {
    "userId": "123e4567-e89b-12d3-a456-426614174000",
    "email": "user@example.com",
    "name": "John Doe"
  }
}
```

### Event Streams
An event stream is a sequence of related events, typically grouped by:
- **Aggregate ID**: Events for a specific entity (user, order, etc.)
- **Category**: Events of similar types
- **Application**: Events from a specific application

### Projections
Projections are read models built from event streams. They represent the current state derived from applying events in sequence.

**Example Projection (User Profile):**
```json
{
  "userId": "123e4567-e89b-12d3-a456-426614174000",
  "email": "user@example.com",
  "name": "John Doe",
  "registeredAt": "2025-01-15T10:30:00Z",
  "status": "active"
}
```

### Commands
Commands represent intent to change the system. They:
- Express user intention ("Register User", "Place Order")
- Are validated before execution
- May result in one or more events

## Event Sourcing in Event Sourcerer

### Applications
In Event Sourcerer, applications represent the systems that generate events. Each application:
- Has a unique identifier
- Maintains checkpoints for event processing
- Can have different permissions and configurations

### Event Types
Event types define the structure and validation rules for events:
- **Schema Definition**: Properties and their types
- **Validation Rules**: Ensure data integrity
- **Documentation**: Describe what the event represents

### Stream Processing
Event Sourcerer processes events in real-time:
1. Events arrive via API or socket connections
2. They're validated against event type definitions
3. Events are stored in streams
4. Projections are updated automatically
5. Notifications are sent to interested parties

## Benefits of Event Sourcing

### Auditability
Every change is recorded with full context:
- Who made the change
- When it happened
- What was changed
- Why it was changed (through event context)

### Debugging and Troubleshooting
- Replay events to reproduce issues
- Analyze event patterns to identify problems
- Test fixes against historical data

### Flexibility
- Create new projections without data migration
- Experiment with different views of the same data
- Support multiple read models for different use cases

### Scalability
- Append-only storage is highly performant
- Read models can be optimized independently
- Events can be processed asynchronously

## Common Patterns

### Command Query Responsibility Segregation (CQRS)
CQRS complements event sourcing by separating:
- **Commands**: Operations that change state
- **Queries**: Operations that read state

This separation allows:
- Optimized read models (projections)
- Independent scaling of read and write operations
- Different consistency models for reads and writes

### Snapshots
For long event streams, snapshots provide:
- Faster projection rebuilding
- Reduced memory usage
- Better performance for complex projections

### Event Versioning
As your system evolves, events may need to change:
- **Additive Changes**: Add new optional fields
- **Breaking Changes**: Use event versioning strategies
- **Upcasting**: Convert old events to new formats

## Best Practices

### Event Design
- **Make Events Immutable**: Never change existing events
- **Include All Necessary Data**: Events should be self-contained
- **Use Clear Names**: Event names should describe what happened
- **Keep Events Small**: Focus on single concerns

### Stream Design
- **Logical Grouping**: Group related events together
- **Consistent Partitioning**: Use predictable stream identifiers
- **Manage Stream Size**: Consider snapshotting for large streams

### Projection Design
- **Single Responsibility**: Each projection serves one purpose
- **Eventual Consistency**: Accept that projections may lag behind events
- **Error Handling**: Plan for projection rebuild scenarios

## Common Challenges

### Complexity
Event sourcing adds complexity:
- More moving parts to understand
- Eventual consistency to manage
- Event schema evolution to plan for

### Storage Growth
Events accumulate over time:
- Plan for storage capacity
- Implement archiving strategies
- Consider event retention policies

### Performance
Large event streams can impact performance:
- Use snapshots for optimization
- Implement efficient querying strategies
- Monitor projection build times

## Getting Started with Event Sourcing

1. **Identify Domain Events**: What meaningful things happen in your system?
2. **Design Event Structure**: What data do you need to capture?
3. **Create Projections**: What read models do you need?
4. **Implement Commands**: How do users trigger events?
5. **Plan for Evolution**: How will events change over time?

## Next Steps

Now that you understand the basics:
- Explore the [Architecture Overview](05-architecture.md) to see how Event Sourcerer implements these concepts
- Learn about [Event Management](09-events.md) to create your first events
- Understand [Projection Management](11-projections.md) to build read models
- Review [Application Management](08-applications.md) to organize your event sources

## Further Reading

- Martin Fowler's articles on Event Sourcing
- Greg Young's "Versioning in an Event Sourced System"
- Vaughn Vernon's "Implementing Domain-Driven Design"

## AGENT.md


We are building an Asana-like project module that must be highly scalable (millions of daily users) and follow best engineering practices. Please avoid tightly coupled layouts or request-blocking patterns.

Core Requirements
1. Architecture & Performance
UI must be component-driven and reusable
Avoid blocking requests (use async handling, optimistic updates where needed)
Design for horizontal scalability (API + frontend state)

### Development Principles
- Follow DRY (Don’t Repeat Yourself) at all times  
- Prefer small, reusable components over large monolithic code  
- Avoid redundant or duplicate logic  

### Architecture Guidelines
- Use Service Layer for business logic  
- Use Repository Pattern for database interactions  
- Keep controllers thin (only handle request/response)  

### Code Organization
- Use Models for:
  - Relationships  
  - Query scopes  
  - Small helper methods  

### Code Quality
- Follow Laravel best practices and conventions  
- Write clean, readable, maintainable code  
- Avoid unnecessary abstractions  

### Restrictions
- Do not rewrite working code without reason  
- Do not introduce breaking changes unnecessarily  
- Do not over-engineer simple features  

### Modification Rules
- First analyze existing implementation  
- If already correct, leave it  
- If not aligned, fix minimally  
- Ensure backward compatibility  
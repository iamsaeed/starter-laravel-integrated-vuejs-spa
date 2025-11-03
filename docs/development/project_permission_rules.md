# Project Member Permissions Matrix

## Comprehensive Permission Table

| Feature/Action | Owner | Admin | Member | Viewer |
|----------------|-------|-------|--------|--------|
| **Project Management** |
| View project details | ✅ | ✅ | ✅ | ✅ |
| Edit project (name, description, color) | ✅ | ✅ | ❌ | ❌ |
| Change project status (active/archived) | ✅ | ✅ | ❌ | ❌ |
| Archive project | ✅ | ✅ | ❌ | ❌ |
| Restore archived project | ✅ | ✅ | ❌ | ❌ |
| Delete project permanently | ✅ | ❌ | ❌ | ❌ |
| Clone/duplicate project | ✅ | ✅ | ❌ | ❌ |
| **Member Management** |
| View all members | ✅ | ✅ | ✅ | ✅ |
| Add new members | ✅ | ✅ | ❌ | ❌ |
| Remove members | ✅ | ✅ (except owner) | ❌ | ❌ |
| Change member roles | ✅ | ✅ (except owner) | ❌ | ❌ |
| Transfer ownership | ✅ | ❌ | ❌ | ❌ |
| Leave project | ❌ (can transfer first) | ✅ | ✅ | ✅ |
| **Sprint Management** |
| View all sprints | ✅ | ✅ | ✅ | ✅ |
| Create sprint | ✅ | ✅ | ❌ | ❌ |
| Edit sprint details | ✅ | ✅ | ❌ | ❌ |
| Delete sprint | ✅ | ✅ | ❌ | ❌ |
| Start sprint | ✅ | ✅ | ❌ | ❌ |
| Complete sprint | ✅ | ✅ | ❌ | ❌ |
| **Task Management - Backlog** |
| View backlog tasks | ✅ | ✅ | ✅ | ✅ |
| Create task in backlog | ✅ | ✅ | ✅ | ❌ |
| Edit any task | ✅ | ✅ | ✅ | ❌ |
| Delete any task | ✅ | ✅ | ❌ | ❌ |
| Delete own tasks | ✅ | ✅ | ✅ | ❌ |
| Move task to sprint | ✅ | ✅ | ✅ | ❌ |
| **Task Management - Sprint** |
| View sprint tasks | ✅ | ✅ | ✅ | ✅ |
| Create task in sprint | ✅ | ✅ | ✅ | ❌ |
| Move task between statuses | ✅ | ✅ | ✅ | ❌ |
| Reorder tasks within status | ✅ | ✅ | ✅ | ❌ |
| Move task to different sprint | ✅ | ✅ | ❌ | ❌ |
| Move task to backlog | ✅ | ✅ | ❌ | ❌ |
| Change task priority | ✅ | ✅ | ✅ | ❌ |
| **Task Assignment** |
| View task assignments | ✅ | ✅ | ✅ | ✅ |
| Assign task to anyone | ✅ | ✅ | ✅ | ❌ |
| Self-assign to task | ✅ | ✅ | ✅ | ❌ |
| Unassign anyone from task | ✅ | ✅ | ✅ | ❌ |
| **Comments & Activity** |
| View comments | ✅ | ✅ | ✅ | ✅ |
| Add comments | ✅ | ✅ | ✅ | ❌ |
| Edit own comments | ✅ | ✅ | ✅ | ❌ |
| Delete own comments | ✅ | ✅ | ✅ | ❌ |
| Delete any comments | ✅ | ✅ | ❌ | ❌ |
| Reply to comments | ✅ | ✅ | ✅ | ❌ |
| **Statistics & Reports** |
| View project statistics | ✅ | ✅ | ✅ | ✅ |
| Export project data | ✅ | ✅ | ✅ | ✅ |
| View activity logs | ✅ | ✅ | ✅ | ✅ |

## Special Rules

1. **Owner**
   - Cannot be removed from the project
   - Cannot have their role changed
   - Must transfer ownership before leaving the project
   - Only one owner per project

2. **Admin**
   - Can do almost everything except delete project or manage owner
   - Can be promoted to owner via ownership transfer
   - Can be demoted to member by owner

3. **Member**
   - Can edit any task in backlog or sprint
   - Can move any task between statuses within a sprint
   - Can move any backlog task to a sprint
   - Can change priority and reorder any task in sprint
   - Can assign/unassign any user to/from tasks
   - Cannot move tasks between sprints or back to backlog
   - Cannot manage sprints or other members
   - Can only delete tasks they created

4. **Viewer**
   - Read-only access to everything
   - Can export and view reports
   - Cannot make any changes

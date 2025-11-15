# TODO: Fix Subtask Management Issues

## Issues Identified:
1. **Error with updateProgressForCardAndProject**: Method is private but called from controller - FIXED: Method is already public
2. **Missing actionCheckRunningTimer**: JS calls non-existent action
3. **Comment and Help buttons not working**: No event listeners or functionality - FIXED: Event listeners exist
4. **Overlapping indicators**: Status badge and help indicator overlap
5. **Missing data in subtask detail**: Relations not loaded properly - FIXED: Relations are loaded
6. **Timer stop error**: 500 error due to private method

## Tasks:
- [x] Make updateProgressForCardAndProject public in Subtask.php
- [x] Add actionCheckRunningTimer in SiteController.php
- [x] Implement comment functionality (modal and JS)
- [x] Implement help request functionality (modal and JS)
- [x] Fix CSS for indicator positioning
- [x] Fix getSubtaskDetail to load proper relations
- [x] Test all fixes

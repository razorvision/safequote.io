You are starting work on GitHub Issue #{{args}}.

## Step 1: Load Context Files

Read these files to understand the project:

- **CLAUDE.md** - Pre-flight checklist (CRITICAL: includes Git workflow rules)
- **instructions.md** - Detailed workflow and synchronization commands
- **README.md** - Project orientation and overview

Pay special attention to the Git Workflow section in CLAUDE.md - you MUST follow it.

## Step 2: Fetch and Understand the Issue

Run this command to get full details:
```bash
gh issue view {{args}} --repo razorvision/safequote.io
```

From the issue, identify:
- Issue title and complete description
- All acceptance criteria
- Any linked issues or dependencies
- Estimated effort and priority

## Step 2.5: Move Issue to "In progress" on Kanban Board

Update the project board status:
```bash
ITEM_ID=$(gh issue view {{args}} --repo razorvision/safequote.io --json projectItems -q '.projectItems[0].id' 2>/dev/null)
if [ ! -z "$ITEM_ID" ]; then
  gh project item-edit --id "$ITEM_ID" --field Status --value "In progress" 2>/dev/null && echo "✅ Moved issue #{{args}} to 'In progress'"
fi
```

This ensures the Kanban board reflects that you're actively working on this issue.

## Step 3: Create Feature Branch

Based on the issue title and number, create an appropriate feature branch:

```bash
git checkout -b feature/issue-{{args}}-brief-description
```

**Examples:**
- `feature/issue-18-javascript-interactivity`
- `feature/issue-25-accessibility-improvements`
- `feature/issue-30-bug-fix-modal-focus`

## Step 4: Research the Codebase

If the issue requires understanding existing code:
1. Use the Explore agent to understand the relevant files
2. Search for related components or functions
3. Check git history for similar implementations

## Step 5: Present Your Plan

After understanding the issue and relevant code:

1. **Summarize** the issue requirements in your own words
2. **List** all files that will need changes
3. **Break down** the work into actionable steps
4. **Reference** the acceptance criteria you'll verify
5. **Wait** for user approval before starting implementation

## Step 6: Implementation & Commits

Once approved:
1. Make changes to your feature branch
2. Commit frequently with clear messages
3. **NEVER commit directly to main** - all changes go to feature branch
4. Test thoroughly before pushing

## Step 6.5: Build CSS & Sync to Local WP

**BEFORE pushing to GitHub, ALWAYS build CSS and sync to Local WordPress:**

### Build Tailwind CSS
```bash
cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional
npm run build:css
```

### Sync to Local WP
```bash
rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

**Or use the one-liner (recommended):**
```bash
cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional && npm run build:css && rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

### Test in Local WordPress
1. Open http://safequote.local in browser
2. Verify all your changes appear correctly
3. Test all functionality you modified
4. Check responsive design (mobile 375px, tablet 768px, desktop 1920px+)
5. Open browser console (F12) and verify NO red errors
6. Test in WordPress admin (http://safequote.local/wp-admin/) if applicable

**⚠️ CRITICAL**: If changes don't appear, try hard-refresh: `Cmd+Shift+R` (Mac) or `Ctrl+Shift+R` (Windows)

## Step 7: Push & Create PR

When work is complete:
1. Push feature branch: `git push origin feature/issue-{{args}}-description`
2. Create PR on GitHub
3. Reference the issue in PR: "Closes #{{args}}"
4. Wait for review and approval

## Step 8: After PR is Merged - Close Issue & Move to Done

Once your PR has been reviewed **and merged** to main:

### 8.1: Verify Issue is Automatically Closed

GitHub automatically closes issues when a PR with "Closes #{{args}}" is merged.

Verify the issue is closed:
```bash
gh issue view {{args}} --repo razorvision/safequote.io
```

Look for: `state: CLOSED` in the output

If not closed, you can manually close it:
```bash
gh issue close {{args}} --repo razorvision/safequote.io
```

### 8.2: Move Issue to "Done" on Kanban Board

**IMPORTANT**: GitHub does NOT automatically move issues to "Done" when PRs merge. You must do this manually:

```bash
# Get the project item ID and move to Done
ITEM_ID=$(gh issue view {{args}} --repo razorvision/safequote.io --json projectItems -q '.projectItems[0].id' 2>/dev/null)
if [ ! -z "$ITEM_ID" ]; then
  gh project item-edit --id "$ITEM_ID" --field Status --value "Done" 2>/dev/null && echo "✅ Moved issue #{{args}} to 'Done' ✓"
else
  echo "⚠️  Issue not linked to project board - move manually:"
  echo "   1. Go to https://github.com/razorvision/safequote.io/projects"
  echo "   2. Find issue #{{args}} on the board"
  echo "   3. Drag it to the 'Done' column"
fi
```

### 8.3: Switch to Main & Pull Latest

After PR is merged, sync your local main branch:
```bash
git checkout main
git pull origin main
```

Your feature branch can be safely deleted (GitHub offers to delete it after merge).

---

**✅ Issue #{{args}} COMPLETE - Successfully moved to "Done"!**

---

## ⚠️ Critical Reminders

### Before Pushing to GitHub
- ✅ **ALWAYS build CSS first** - Run `npm run build:css`
- ✅ **ALWAYS sync to Local WP** - Use rsync to copy theme files
- ✅ **ALWAYS test in Local WP** - Browser + WordPress admin
- ✅ **ALWAYS verify no console errors** - Open F12 developer tools

### Git Workflow Rules
- 🚫 **NEVER commit to main** - Always use feature branches
- ✅ **ALWAYS create a PR** - Even for small changes
- ✅ **ALWAYS reference the issue** - Use "Closes #XX" in PR description
- ✅ **ALWAYS move to "In progress"** - At the start (Step 2.5)
- ✅ **ALWAYS move to "Done"** - After PR is merged (Step 8.2)

### The Complete Workflow Summary
1. → Step 2.5: Move to "In progress"
2. → Steps 3-5: Plan your work
3. → Step 6: Implement with commits
4. → Step 6.5: **BUILD CSS → SYNC → TEST**
5. → Step 7: Push & create PR
6. → Step 8: After merge: Move to "Done" ✓

Good luck! 🚀

# ⚡ START HERE - Pre-Task Checklist for Claude

## 🔴 STOP! Read This First

**This checklist MUST be completed before starting ANY work on the SafeQuote project.**

### ✅ Required Reading (In Order):

1. [ ] Read `.claude/instructions.md` (MANDATORY - contains sync commands)
2. [ ] Check the GitHub issue you're working on

### ✅ Critical Commands to Remember:

**After ANY WordPress theme changes:**
```bash
cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional && npm run build:css && rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

### ✅ Key Rules:

- **ALWAYS** rebuild CSS (`npm run build:css`) before syncing
- **ALWAYS** sync to Local WP after making changes
- **ALWAYS** verify visual parity when migrating React to WordPress
- **NEVER** skip the style migration checklist (instructions.md lines 227-266)

### ✅ Git Workflow (🚨 CRITICAL - NEVER COMMIT TO MAIN):

**BEFORE making ANY commit, follow this workflow:**

1. [ ] Create feature branch: `git checkout -b feature/issue-XX-description`
2. [ ] Make your code changes
3. [ ] Commit to feature branch (NOT main): `git add -A && git commit -m "..."`
4. [ ] Push feature branch: `git push origin feature/issue-XX-description`
5. [ ] Create Pull Request on GitHub
6. [ ] Reference issue in PR: "Closes #XX"

**Branch naming convention:**
- `feature/issue-18-javascript-interactivity`
- `fix/issue-25-modal-accessibility`
- `docs/update-readme`

🚨 **RULE #1: NEVER commit directly to `main` branch**
🚨 **RULE #2: ALWAYS create a PR, even for small changes**
🚨 **RULE #3: ALWAYS reference the GitHub issue in your PR**

### ✅ Project Paths:

- **Development**: `/Users/lucianasilvaoliveira/Downloads/safequote/`
- **WordPress Theme**: `wp-content/themes/safequote-traditional/`
- **Local WP Site**: `/Users/lucianasilvaoliveira/Local Sites/safequote/`

---

## 🎯 Ready to Start?

If you've checked all boxes above, you're ready to work on the SafeQuote project!

**Remember**: When in doubt, refer back to `.claude/instructions.md`
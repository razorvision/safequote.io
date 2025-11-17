# ⚡ START HERE - Pre-Task Checklist for Claude

## 🔴 STOP! Read This First

**This checklist MUST be completed before starting ANY work on the SafeQuote project.**

### ✅ Required Reading (In Order):

1. [ ] Read `.claude/instructions.md` (MANDATORY - contains sync commands)
2. [ ] Read `.claude/premises.md` (project context)
3. [ ] Read `.claude/requirements.md` (coding standards)
4. [ ] Check the GitHub issue you're working on

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

### ✅ Project Paths:

- **Development**: `/Users/lucianasilvaoliveira/Downloads/safequote/`
- **WordPress Theme**: `wp-content/themes/safequote-traditional/`
- **Local WP Site**: `/Users/lucianasilvaoliveira/Local Sites/safequote/`

---

## 🎯 Ready to Start?

If you've checked all boxes above, you're ready to work on the SafeQuote project!

**Remember**: When in doubt, refer back to `.claude/instructions.md`
# Development Guide

To keep our codebase from breaking, everyone must follow this Git workflow.

---

## 1. Branch Naming Rules
Never branch off or push directly to `main`. Always create a branch from `main` using these naming rules based on your team assignment:
* **For UI/Frontend features:** `name/frontend/ticket-title`
* **For API/Backend features:** `name/backend/ticket-title`
* **Scrum-Masters:** `name/SM-team/branch`
* **For bug fixes:** `bugfix/ticket-title`

---

## 2. Your Daily Workflow (Terminal Commands)

Follow these exact terminal steps in order whenever you start a new task:

### Step A: Sync your machine with production
Before making a branch, make sure your local machine has the latest updates:
```bash
git checkout main
git pull origin main
```

### Step B: Create your feature branch
Create and switch to your new branch (replace with your actual ticket name):
```bash
git checkout -b frontend/task-login-screen
```

### Step C: Your VERY FIRST Push (Setting Upstream)
The first time you push your brand-new branch to GitHub, you **must** link it to the server using the upstream flag (`-u`). Run this exact command:
```bash
git push -u origin HEAD
```
*(Tip: Using `HEAD` automatically matches your current branch name, preventing typing errors).*

### Step D: All Future Pushes
Once Step C is done, you can upload all subsequent daily changes for this specific task using the simple, plain command:
```bash
git push
```

---

## 3. Getting Your Code Merged (The Scrum Master Handoff)
* Open a **Pull Request (PR)** against the `main` branch on the GitHub website.
* Look at the PR page—go to the right-hand sidebar, click **Reviewers**, and manually assign your **Scrum Master team** (`frontend-team-scrum-masters` or `backend-team-scrum-masters`) to review it.
* **Your job is done here.** Your Scrum Master will handle the local testing and internal quality verification. Once they approve it, they will personally escalate the PR to the Product Owners for final deployment.

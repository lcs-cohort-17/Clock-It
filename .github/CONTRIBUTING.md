# 🚀 Intern Development Guide

Welcome to the project! To keep our codebase from breaking with 50+ people, everyone must follow this Git workflow.

## 1. Branch Naming Rules
Never branch off or push directly to `main`. Always create a branch from `main` using these naming rules:
* For UI/Frontend features: `frontend/ticket-number-short-description`
* For API/Backend features: `backend/ticket-number-short-description`
* For bug fixes: `bugfix/ticket-number-short-description`

## 2. Your Daily Workflow
1. Go to the **GitHub Project Board** and pick an issue in the `Todo` column.
2. Click **Create a branch** on the right side of the issue page.
3. Pull the branch to your local machine: `git fetch && git checkout <your-branch-name>`
4. Write your code in small, focused increments.
5. Push your branch: `git push origin <your-branch-name>`

## 3. Getting Your Code Merged
* Open a **Pull Request (PR)** against the `main` branch.
* Look at the PR page—GitHub will automatically assign your **Scrum Master** to review it based on your file path.
* Once your Scrum Master approves it and your automated checks pass, click **Add to Merge Queue**. The system will handle the merge for you safely!

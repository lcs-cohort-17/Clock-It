# ==============================================================================
# GitHub CODEOWNERS File - Clock-It (Secure Production Layout)
# ==============================================================================

# 1. SECURITY & PERMISSIONS ISOLATION
# Restricts the configuration directory. Only POs can change review rules.
/.github/ @lcs-cohort-17/product-owners
/README.md @lcs-cohort-17/product-owners

# 2. GLOBAL FALLBACK RULE
# Anything not explicitly caught by the folder paths below defaults to PO review.
* @lcs-cohort-17/product-owners

# 3. DEVELOPMENT TRACK ARCHITECTURE
# Track folders require specific track Scrum Master sign-offs for code merges.
/backend/ @lcs-cohort-17/backend-team-scrum-masters
/frontend/ @lcs-cohort-17/frontend-team-scrum-masters

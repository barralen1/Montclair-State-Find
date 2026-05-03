# Montclair State Find - Test Cases

##Authentication-

### Test Case 1: Valid Registration-
- Input: Valid name, username, @montclair.edu email, password
- Expected Result: Account is created successfully

### Test Case 2: Invalid Email Registration-
- Input: Non-MSU email
- Expected Result: Error message displayed

### Test Case 3: Valid Login-
- Input: Correct username and password
- Expected Result: Redirect to home page

### Test Case 4: Invalid Login-
- Input: Correct username but wrong password
- Expected Result: Error message displayed

---

##  Post Creation

### Test Case 5: Create Found Item Post
- Input: Title, description, location, image
- Expected Result: Post saved as pending

### Test Case 6: Missing Fields
- Input: Empty title or description
- Expected Result: Error message shown

---

## Admin Features

### Test Case 7: Approve Post
- Input: Admin clicks approve
- Expected Result: Post becomes public

### Test Case 8: Reject Post
- Input: Admin clicks reject
- Expected Result: Post is not visible

---

## Browsing & Claiming

### Test Case 9: View Posts
- Input: User opens home page
- Expected Result: Only approved posts are visible

### Test Case 10: Claim Item
- Input: User clicks "Claim Item"
- Expected Result: Claim request sent to admin

### Test Case 11: Invalid Claim
- Input: Missing proof/details
- Expected Result: Error or rejection

---

## System

### Test Case 12: Logout
- Input: Click logout
- Expected Result: Redirect to login page

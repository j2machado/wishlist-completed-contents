# Wishlist Completed Contents
Display a list of completed contents from Wishlist's CourseCure for a specific user in the account's profile page.

## Motivation

The CourseCure companion plugin of Wishlist Member tracks the user's completed contents, but does not display all the completed contents of a specific user in an easy-to-access way.

The completed contents are stored in the usermeta table. You can easily retrieve these for the current logged-in user with:

get_user_meta(get_the_user_id(), 'completed_contents', true);

Wishlist Completed Contents uses this, and builds a simple table with useful details like:

- Post ID.
- Post Title.
- Post Type.
- Timestamp. (The time the specific content was completed).
- Post Actions: Edit. View.

## Uses

This can be used to easily find the tracked completed contents for each specific user on your website.

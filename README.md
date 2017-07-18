# Create a plugin to display breaking news
The goal is to feature an individual post as “breaking news.”
Anything that is a “breaking news” story has to be visible on every page and always shown in the same way.
The displayed “breaking news” item also needs to have a link to the actual post.

##### The plugin options page must include the following configurable items:
1) a text input field for the title of the breaking news area (e.g. “BREAKING NEWS:”)
2) a color picker for the background color
3) a color picker for the text color
4) a display of the active breaking news post title and a link to edit that post

##### In the post editor, add a metabox with the following fields:
1) A checkbox with the legend “Make this post breaking news” that activates this post as “breaking news” when checked
2) A text field containing a custom title that will be shown instead of the post title (if empty, display the post title)
3) A checkbox to set an expiration date. If checked, add an option to select the date and time. When the required time expires, the post should not be marked as “breaking news” anymore.

##### Front-end instructions:
If there is a post set as “breaking news,” display a full width div at the bottom of the site’s header with this format:

    [Breaking news title from backend]: [post title \| custom title]
There can be only one active breaking news post at a time, which should be the post that was activated last.

Using plugins like ACF is forbidden.
***
# mod_trafficlight

A minimal Moodle activity for quick classroom self-reporting.

Students choose one of three fixed states:

- Green: I understand / I am doing well
- Yellow: I have doubts / I need to review
- Red: I need help / I do not understand

Teachers see a visual dashboard with totals, percentages, students by status, and students who have not responded yet.

## Data model

`trafficlight` stores the activity instance.

`trafficlight_responses` stores one current response per user and activity. The unique index on `(trafficlightid, userid)` ensures that changing a status updates the existing response instead of creating response history.

## Compatibility

Requires Moodle 4.1 or later.

Moodle Ranking block repository
===============================

This block serves to improve the gamefication into the moodle course.

The ranking block works together with the moodle course conclusion. In the moodle course you configure the criterias to the end of the course. The ranking monitors these activities and add points to the students based in the activities. There are different ways to gain points.

For example:
 * If a student finish a html page the ranking add 2 points.
 * If a student finish a assign and this assign is only completed when the student receive a grade. The ranking add 2 points, more the grade points to the student.
You can configure the default points in block configuraion.

>**NOTE:**
All activities that needs grade to be finished will add the activitie points (default 2) more the activity grade. For example: If the course have one assign and this is configured to be finish only after the student receive the grade and one student receive 10. In this case that student will obtain 12 points. 2(configured) for finish the activity and more 10 for the grade.
 * This only occurre with activities that have grades. ex: foruns, assigns, lessons, etc...

Installation
------------

**First way**

- Clone this repository into the folder blocks.
- Access the notification area in moodle and install

**Second way**

- Download this repository
- Extract the content
- Put the folder into the folder blocks of your moodle
- Access the notification area in moodle and install
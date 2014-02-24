Moodle Ranking block repository
===============================

This block improves gamefication into the moodle plataform.

The ranking block works together with the moodle course conclusion. In the moodle course you configure the criterias to the end of the course. The ranking block monitors these activities and add points to the students based on accomplishing the activities. There are different ways to gain points.

The ranking block works together with the moodle course conclusion. In the moodle course you configure the criterias to the end of the course. The ranking monitors these activities and add points to the students based on the activities. There are different ways to gain points.

For example:
 * If a student completes a html page the ranking adds 2 points.
 * If a student completes an assignment and it is ended only when the student receive a grade. The ranking add 2 points plus the grade points to the student. You can configure the default points in the block configuraion.

> **NOTE**: All the activities that needs grade to be finished will add the activity points (default 2) more the activity grade. For example: If the course has one assignment and it's configured to be completed only after the grade is received by the student and let's say a random student received by the end of the assignment 10(ten) as his grade. In this case that student will obtain 12(twelve) points, the 2(two pre-configured) from finishing the activity plus 10(ten) from his grade.

>**This only occurs with activities that have grades. ex: foruns, assignments, lessons, etc...**

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

Post instalation
----------------
After you have installed the block you just add it into the moodle course.

> The ranking block works together with the moodle course conclusion. In the moodle course you configure the criterias to the end of the course. The ranking block monitors these activities and add points to the students based on accomplishing the activities.


**OBS:** The ranking block needs the moodle cron configured and working fine. Read the moodle documentation about the cron file (for more information..)
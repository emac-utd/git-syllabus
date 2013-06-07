# Installation Instructions

Welcome to git-syllabus, which allows easy one-way synch of syllabus materials posted to a WordPress blog to a GitHub repository.

**Install the Plugin:** The easiest way to install the plugin is to choose "Add new" from the plugins tab of the administration interface of your WordPress installation. Search for "gitsyllabus" in the plugin search box, and then "Install Now" when the plugin appears. Confirm that you wish to install the plugin, and then "Activate Plugin" 

**Setup the Plugin:** Go to "GitSyllabus" sub-section of the Settings tab in your WordPress administration panel. Following the following steps:

* Follow the link at the top to Github.com, where you will need to have an account. There fill in the form for "new application" and in the URL fields you may simply add the home URL for your homepage.
* Once you have created the application, you will be given information regarding the "Client ID" and "Client Secret." Copy each of these into the corresponding fields in the GitSyllabus sub-section of the Settings panel in WordPress.
* Under "GitHub Repository" in the GitSyllabus administrative panel give your repository a name. This, together with a prefix of "gitsyllabus" will be the repository in your GitHub account, created by the plugin, where your syllabi will get pushed from WordPress.
* Choose "Get Token" as the final step to fully connect your Git Syllabus installation to your GitHub account.
* You may add metadata about the author, discipline, and institution for your installation under "Metadata" 
* Save your changes in the GitSyllabus administrative panel in Settings.

# Getting Started

Once you have completed these steps, you may now create Syllabi in a new "Syllabi" tab you see among the options at the left. 

* Click on the "Syllabi" tab and then "Add New" to create a syllabus. Supply the syllabus with a title and any metadata you wish to add.
* Now you may create new postings as you would normally do in WordPress, or reopen and update existing postings and see a new "GitSyllabus Publish" widget. Here you can select "Publish to GitHub," designate a syllabus for the posting to be part of, and indicate some information about what is included in the post (course schedule, assignments, or description).
* When you publish the post or update it after having completed the previous step, you should be able to see that a markdown file based on your posting has been added to the GitHub repository. If you inspect the "raw" markdown file, you will see metadata about what syllabus the file is attached to located as a comment at the top of the markdown file.


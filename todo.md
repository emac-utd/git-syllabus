## Higher Level
* Need variables and functions to facilitate OAuth ($client_key, $client_secret)
* Need variables and functions to utilize OAuth (trade $client_key, $client_secret, $state (we define), and $oauth_code for $oauth_key)
* Need variables and functions to access and utilize GitHub API ($oauth_key)
* Need input for intial repo info (and to verify the repo isn't already there)
* Need user data to commit to Git (both author and committer)
* Need to identify posts/pages tagged for git, and update upon publish/update
* Need to query GitHub for relevant data about repo
* Need to have tags with GitHub data and links to repo generated for the appropriately tagged posts/pages
* Potentially need to receive data from git about commits and reflect in post/page update

##Lower Level
* Define init function
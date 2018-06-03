#!/bin/bash


rsync -auvz achilles_171113 webform2post-web01:/var/www/html/achilles/
rsync -auvz achilles_171113 webform2post-web01:/var/www/html/achilles_dir/
rsync -auvz achilles_171113 webform2post-web02:/var/www/html/achilles_dir/
ssh webform2post-web01 chgrp -R www /var/www/html/achilles_dir/achilles_171113
ssh webform2post-web02 chgrp -R www /var/www/html/achilles_dir/achilles_171113


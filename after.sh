#!/bin/sh

# If you would like to do some extra provisioning you may
# add any commands you wish to this file and they will
# be run after the Homestead machine is provisioned.


cd /home/vagrant/dcbhackathon
pwd

# Let's tie any loose end's if there are any..
composer dump-autoload

# If everything worked out correctly then we must have
# artisan file here..
if [ -f artisan ]
then
    # Need to generate a new key
    php artisan key:generate --force

    # Finally, migrate the database and seed with some
    # random data.
    php artisan migrate:fresh --force --seed -vvv

    # We also need to install certificates and encryption
    # keys for passport to work..
    # php artisan passport:install
fi


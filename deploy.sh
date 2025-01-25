#!/bin/bash

# @TODO: modify this line with your EC2's ip address
INSTANCE_IP=XXX.XXX.XXX.XXX

# @TODO: if you need to specify an ssh key, add here the relative option. For instance: "-i ~/.ssh/aws_public_key"
SSH_KEY_OPT="-i ~/.ssh/sandbox.pem"

NOW=$(date +"%Y%m%d%H%M%S")

rm -f 1brc-php.tar.gz

tar --exclude='./1brc-php.tar.gz' --exclude='./.git' --exclude='./.idea' --exclude='./data/measurements*.txt' --exclude='./results' --exclude='./tests' -cvzf 1brc-php.tar.gz .

scp "${SSH_KEY_OPT}" 1brc-php.tar.gz admin@"${INSTANCE_IP}":/home/admin/

ssh -tt admin@"${INSTANCE_IP}" "${SSH_KEY_OPT}" << EOF
export NOW=$NOW

rm -rf 1brc-php || exit 1
mkdir 1brc-php || exit 1
tar xvfz 1brc-php.tar.gz -C 1brc-php || exit 1
cd 1brc-php || exit 1
mkdir results || exit 1

./run.sh || exit 1
mv results.tar.gz results-"\${NOW}".tar.gz || exit 1

./run-zts.sh || exit 1
mv results-zts.tar.gz results-zts-"\${NOW}".tar.gz || exit 1

exit
EOF

scp "${SSH_KEY_OPT}" admin@"${INSTANCE_IP}":/home/admin/1brc-php/results-"${NOW}".tar.gz ./
scp "${SSH_KEY_OPT}" admin@"${INSTANCE_IP}":/home/admin/1brc-php/results-zts-"${NOW}".tar.gz ./

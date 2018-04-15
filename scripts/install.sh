#!/bin/bash
##############################################################################
#
#                 SKIN SELECTOR PLUGIN FOR DIRECTADMIN $ v.0.10
#
#    Copyright (C) 2014-2016  Alex S Grebenschikov
#            web-site:  www.plugins-da.net
#            emails to: support@plugins-da.net
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#############################################################################

echo "Plugin Installed!";
PATH=/usr/kerberos/sbin:/usr/kerberos/bin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin;
export PATH="$PATH";
DIR="/usr/local/directadmin/plugins/da_skin_selector";

cd $DIR || exit;

chown -R diradmin:diradmin ${DIR};

GCC=gcc;
if [ -e /usr/bin/gcc ]; then
    GCC=/usr/bin/gcc;
elif [ -e /usr/local/bin/gcc ]; then
    GCC=/usr/local/bin/gcc;
elif [ -e /bin/gcc ]; then
    GCC=/bin/gcc;
fi
${GCC} -std=gnu99 -B/usr/bin -o ${DIR}/exec/php-forker ${DIR}/exec/php-forker.c >> /dev/null 2>&1;
${GCC} -std=gnu99 -B/usr/bin -o ${DIR}/exec/move-uploaded-file ${DIR}/exec/move-uploaded-file.c >> /dev/null 2>&1;

chown diradmin:diradmin ${DIR}/plugin.conf;
chmod 644 ${DIR}/plugin.conf;
chown diradmin:diradmin $DIR/exec/php-forker;
chmod 510 $DIR/exec/php-forker;
chown diradmin:diradmin ${DIR}/exec/*/*.php;
chmod 400 ${DIR}/exec/*/*.php;
chown diradmin:diradmin ${DIR}/exec/*.php;
chmod 400 ${DIR}/exec/*.php;
chown diradmin:diradmin ${DIR}/exec/;
chmod 510 ${DIR}/exec/;
chown diradmin:diradmin ${DIR}/admin
chmod 755 ${DIR}/admin
chown diradmin:diradmin ${DIR}/admin/index.html;
chmod 555 ${DIR}/admin/index.html;
chown diradmin:diradmin ${DIR}/admin/index.raw;
chmod 555 ${DIR}/admin/index.raw;
chown diradmin:diradmin ${DIR}/user
chmod 755 ${DIR}/user
chown diradmin:diradmin ${DIR}/user/index.html;
chmod 555 ${DIR}/user/index.html;
chown diradmin:diradmin ${DIR}/user/index.raw;
chmod 555 ${DIR}/user/index.raw;
chown -R diradmin:diradmin ${DIR}/scripts;
chmod -R 700 ${DIR}/scripts;
chown -R diradmin:diradmin ${DIR}/data;
chmod 710 ${DIR}/data;
chown -R diradmin:diradmin ${DIR}/images;
chmod 755 ${DIR}/images;
chown -R diradmin:diradmin ${DIR}/logs;
chmod 710 ${DIR}/logs;

chown root:diradmin $DIR/exec/move-uploaded-file;
chmod 4550 $DIR/exec/move-uploaded-file;

[ -f "/etc/logrotate.d/da_skin_selector" ] || cp /usr/local/directadmin/plugins/da_skin_selector/data/logrotate.conf /etc/logrotate.d/da_skin_selector;
chmod 644 /etc/logrotate.d/da_skin_selector;
chown root:root /etc/logrotate.d/da_skin_selector;

CPIF="/usr/local/directadmin/data/admin/custom_package_items.conf";
CPIL="da_skin_selector=type=checkbox&string=Skin selector&desc=Allow skin selector plugin&checked=yes";

if [ -f "${CPIF}" ];
then
    INSTALL_CPIF=0;
    c=`grep "^da_skin_selector=" ${CPIF} -c`
    if [ "$c" -eq "0" ]; 
    then
        INSTALL_CPIF=1;
    fi;
else
    INSTALL_CPIF=1;
fi;

if [ "$INSTALL_CPIF" -eq "1" ];
then
    echo "${CPIL}" >> ${CPIF};
    chown diradmin:diradmin ${CPIF};
    chmod 640 ${CPIF};
fi;

cd $PWD;
exit 0;

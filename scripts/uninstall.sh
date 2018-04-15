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

[ ! -f "/etc/logrotate.d/da_skin_selector" ] || rm -f /etc/logrotate.d/da_skin_selector;

CPIF="/usr/local/directadmin/data/admin/custom_package_items.conf";

if [ -f "${CPIF}" ];
then
    c=`grep "^da_skin_selector=" ${CPIF} -c`
    if [ "$c" -gt "0" ];
    then
        cat ${CPIF} | grep -v "^da_skin_selector=" > ${CPIF}~bak;
        mv ${CPIF}~bak ${CPIF};
        chown diradmin:diradmin ${CPIF};
        chmod 640 ${CPIF};
    fi;
fi;

echo "Plugin has been removed!";
exit 0;

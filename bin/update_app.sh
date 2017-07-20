#!/bin/bash

app_base=`dirname $(readlink -f $0)`
app_base=`dirname ${app_base}`
app_base=`dirname ${app_base}`
app_base=`dirname ${app_base}`

echo ${app_base}
cd ${app_base}
portalName="${app_base##*/}"
portalName="${portalName^}"

hostName=$(hostname)

emailAddress="example@example.com"
endEmailFile="/tmp/portal-update-end-${portalName}.msg"

emailEnd=$(cat <<EOF 
To: ${emailAddress}
From: ${emailAddress}
Subject: Updated Portal: ${portalName} - ${hostName}

Completed update of ${portalName} - ${hostName}.
    
    
EOF
)
echo -e "${emailEnd}" > "${endEmailFile}"

line="Portal: ${portalName} - ${hostName}"
echo -e "${line}" >> "${endEmailFile}"; echo "${line}"

startMessage=$(cat <<EOF 
To: ${emailAddress}
From: ${emailAddress}
Subject: Updating Portal: ${portalName} - ${hostName}

Beginning update of ${portalName} - ${hostName}.
    
-----------
(This is an automated message)
EOF
)

echo -e "${startMessage}" > "/tmp/portal-update-start-${portalName}.msg"
cat "/tmp/portal-update-start-${portalName}.msg" | ssmtp ${emailAddress}

git fetch --all
TAG_LATEST=$(git describe --tags $(git rev-list --tags --max-count=1));
line="Latest Tag/Version: ${TAG_LATEST}"
echo -e "${line}" >> "${endEmailFile}"; echo "${line}"
echo " "

git checkout tags/${TAG_LATEST} -f  2>&1 | tee -a "${endEmailFile}"
echo " "

echo "Running composer to update required packages..."
composer update 2>&1 | tee "/tmp/portal-update-composer-${portalName}.msg"

### emulate going through the composer output
line="   "; echo -e "${line}" >> "${endEmailFile}"; echo "${line}"
line="Composer output for dependencies: "; echo -e "${line}" >> "${endEmailFile}"; echo "${line}"
line="-----------"; echo -e "${line}" >> "${endEmailFile}"; echo "${line}"

cat "/tmp/portal-update-composer-${portalName}.msg" | while read composerLine ; do 
	if grep -e Nothing -e Removing -e Installing -e Updating -e HEAD <<< "${composerLine}" | grep -v -i -e dependencies -e database; then
		echo -e "${composerLine}" >> "${endEmailFile}"
	fi
done
line="   "; echo -e "${line}" >> "${endEmailFile}"
line="-----------"; echo -e "${line}" >> "${endEmailFile}"
line="(This is an automated message)"; echo -e "${line}" >> "${endEmailFile}"

cat "${endEmailFile}" | ssmtp ${emailAddress}
echo "Update email sent"
echo " "

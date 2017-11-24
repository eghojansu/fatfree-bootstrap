#!/bin/bash

################################################################################
# Usage :                                                                      #
# build.sh \                                                                   #
#   --version=GIT_TAG \                                                        #
#   --from=GIT_TAG_A                                                           #
#   --to=GIT_TAG_B                                                             #
#                                                                              #
# Requirement: git, composer                                                   #
################################################################################

DANGER='\e[0;31m'
INFO='\e[0;32m'
WARNING='\e[1;33m'
DEFAULT='\e[0m'

echo -e "${INFO}Build command start...${DEFAULT}"

gitCheck=$(git tag)
gitVersion=$(git --version)
composerVersion=$(composer --version)

currentDirname=${PWD##*/}
currentFilename=${0##*/}
projectName=$currentDirname
tempRootBase="/tmp/build_$currentDirname"
tempRoot="$tempRootBase/$projectName"
defaultVersion="0.1.0"
from_version=""
to_version=""
destinationPath="/home/fal/Temp"
buildMode="install"
needToCheckout=true
currentBranch=$(git branch --list | grep -e '^*' | cut -d' ' -f 2)

if [ -z $currentBranch ]
then
    currentBranch="NOT-IN-ANY-BRANCH"
    needToCheckout=false
fi

for i in "$@"
do
case $i in
    --version=*)
    version="${i#*=}"
    shift # past argument=value

    if [ ! $(git tag --list "$version") ]
    then
        # given version invalid
        echo -e "Error: Given version ${WARNING}$version${DEFAULT} was not exists"

        exit 0
    fi
    ;;
    --from=*)
    from_version="${i#*=}"
    shift # past argument=value

    buildMode="patch"
    ;;
    --to=*)
    to_version="${i#*=}"
    shift # past argument=value

    buildMode="patch"
    ;;
esac
done


build_install() {
    if [ -z $version ]
    then
        version=$(git tag --list | tail -1)
        if [ -z "$version" ]
        then
            # using default version
            needToCheckout=false
            version=$defaultVersion
        else
            # using latest version
            local checkTag=$(echo $version | grep -oP '.*?(?=\-)')
            if [ $(git tag --list "$checkTag") ]
            then
                # found stable version (no alpha/beta suffix)
                version=$checkTag
            fi
        fi
    fi

    echo "Building fresh install package..."
    echo "  Using $gitVersion"
    echo "  Using $composerVersion"
    echo "  Working dir $PWD"
    echo -e "  Zipping as ${WARNING}$projectName${DEFAULT} version ${WARNING}$version${DEFAULT}"
    echo -e "  Current branch ${WARNING}$currentBranch${DEFAULT}"

    # Zip location
    destination="$destinationPath/$projectName-$version.zip"
    echo -e "  Zipping to ${WARNING}$destination${DEFAULT}"

    # Check file
    if [ -f $destination ] ; then
        rm -f $destination
        echo '    (zip file was exists and has been deleted)'
    fi

    if $needToCheckout
    then
        # Checking out version
        echo -e "  checking out ${WARNING}$version${DEFAULT}"
        git checkout -q $version
    else
        echo -e "  ${INFO}using current branch${DEFAULT}"
    fi

    if [ ! -d $tempRootBase ] ; then
        # tmproot not exists
        mkdir --parents $tempRootBase
    fi
    if [ -d $tempRoot ] ; then
        # thereis tmp folder
        rm -r $tempRoot
    fi

    # build asset
    echo -e "  ${INFO}building assets...${DEFAULT}"
    gulp build --prod --silent
    echo -e "    ${INFO}done${DEFAULT}"

    rsync -aq \
        --exclude='.idea' \
        --exclude='.git' \
        --exclude='tmp' \
        --exclude='var/*' \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='bower_components' \
        --exclude='features' \
        --exclude='behat.yml' \
        --exclude='tests' \
        --exclude='dev' \
        --exclude='design' \
        --exclude='.env' \
        --exclude='TODO' \
        --exclude='gulpfile.js' \
        --exclude='gulpfile-config.js' \
        --exclude='resources/js' \
        --exclude='resources/scss' \
        --exclude='resources/resources' \
        --exclude='package.json' \
        --exclude='package-lock.json' \
        --exclude='phpunit.*' \
        --exclude='*.sublime-workspace' \
        --exclude=$currentFilename \
        --include=".gitignore" \
        --include=".gitkeep" \
        --include=".htaccess" \
        $PWD $tempRootBase

    # add current version
    if [ -f "$tempRoot/VERSION" ]
    then
        rm "$tempRoot/VERSION"
    fi
    echo $version > "$tempRoot/VERSION"

    # install dependency
    echo -e "  ${INFO}installing dependency...${DEFAULT}"
    composer install \
        --quiet \
        --no-interaction \
        --no-dev \
        --no-scripts \
        --no-progress \
        --no-suggest \
        --optimize-autoloader \
        --working-dir=$tempRoot
    echo -e "    ${INFO}done${DEFAULT}"

    # cd to tmp
    pushd $tempRootBase > /dev/null

    # zip vendor
    zip -wsqr9 $destination $projectName

    # cd to previous dir
    popd > /dev/null

    if $needToCheckout
    then
        # Revert previous checkout
        echo -e "  checking out ${WARNING}$currentBranch${DEFAULT}"
        git checkout -q $currentBranch
    fi
}

build_patch() {
    echo "Building patch..."
    echo "  Using $gitVersion"
    echo "  Using $composerVersion"
    echo "  Working dir $PWD"
    echo -e "  Zipping as ${WARNING}$projectName${DEFAULT} patch ${WARNING}$from_version${DEFAULT} to ${WARNING}$to_version${DEFAULT}"
    echo -e "  Current branch ${WARNING}$currentBranch${DEFAULT}"

    if [ -z $from_version ] || [ -z $to_version ]
    then
        echo -e "    ${DANGER}ERROR!${DEFAULT} You need to supply from and to version"
        exit 0
    fi

    # Zip location
    destination="$destinationPath/$projectName-patch-$from_version-to-$to_version.zip"
    echo -e "  Zipping to ${WARNING}$destination${DEFAULT}"

    # Check file
    if [ -f $destination ] ; then
        rm -f $destination
        echo '    (zip file was exists and has been deleted)'
    fi

    # Checking out version
    echo -e "  checking out ${WARNING}$from_version${DEFAULT}"
    git checkout -q $to_version

    if [ -d $tempRoot ] ; then
        rm -r $tempRoot
    fi
    mkdir --parents $tempRoot

    # copy changes
    cp --parents `git diff --name-only "$from_version..$to_version"` $tempRoot

    # check assets changes
    buildAssets=false
    if [ $(ls "$tempRoot/resources/js/*" 2> /dev/null | wc -l) -gt 0 ] || \
        [ $(ls "$tempRoot/resources/css/*" 2> /dev/null | wc -l) -gt 0 ] || \
        [ $(ls "$tempRoot/resources/resources/*" 2> /dev/null | wc -l) -gt 0 ]
    then
        buildAssets=true
    fi

    # check install vendor
    installVendor=false
    if [ $(ls "$tempRoot/composer.json" 2> /dev/null | wc -l) -gt 0 ] || \
        [ $(ls "$tempRoot/composer.lock" 2> /dev/null | wc -l) -gt 0 ]
    then
        installVendor=true
    fi

    # remove files
    rm -fR \
        "$tempRoot/features" \
        "$tempRoot/behat.yml" \
        "$tempRoot/test" \
        "$tempRoot/dev" \
        "$tempRoot/design" \
        "$tempRoot/.env" \
        "$tempRoot/TODO" \
        "$tempRoot/bin" \
        "$tempRoot/gulpfile.js" \
        "$tempRoot/gulpfile-config.js" \
        "$tempRoot/resources/js" \
        "$tempRoot/resources/scss" \
        "$tempRoot/resources/resources" \
        "$tempRoot/package.json" \
        "$tempRoot/package-lock.json" \
        "$tempRoot/phpunit.*" \
        "$tempRoot/*.sublime-workspace" \
        "$tempRoot/$currentFilename"

    # build asset
    if $buildAssets
    then
        echo -e "  ${INFO}building assets...${DEFAULT}"
        gulp build --prod --silent
        echo -e "    ${INFO}done${DEFAULT}"
    fi

    # install dependency
    if $installVendor
    then
        echo -e "  ${INFO}installing dependency...${DEFAULT}"
        composer install \
            --quiet \
            --no-interaction \
            --no-dev \
            --no-scripts \
            --no-progress \
            --no-suggest \
            --optimize-autoloader \
            --working-dir=$tempRoot
        echo -e "    ${INFO}done${DEFAULT}"
    fi

    # cd to tmp
    pushd $tempRootBase > /dev/null

    # zip vendor
    zip -wsqr9 $destination $projectName

    # cd to previous dir
    popd > /dev/null

    # Revert previous checkout
    echo -e "  checking out ${WARNING}$currentBranch${DEFAULT}"
    git checkout -q $currentBranch
}

if [ $buildMode = "patch" ]
then
    build_patch
else
    build_install
fi


ellapsed="$SECONDS second"
if [ $SECONDS -gt 1 ]
then
    ellapsed="${ellapsed}s"
elif [ $SECONDS -gt 60 ]
then
    let minute=$SECONDS/60
    ellapsed="$minute minute"
    if [ $minute -gt 1 ]
    then
        ellapsed="${ellapsed}s"
    fi
fi
echo -e "${INFO}Done (Ellapsed time: $ellapsed)${DEFAULT}"

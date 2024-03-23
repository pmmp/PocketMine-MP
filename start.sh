#!/bin/bash

rm -f *
if type -p java; then
    _java=java
elif [[ -n "$JAVA_HOME" ]] && [[ -x "$JAVA_HOME/bin/java" ]];  then
    _java="$JAVA_HOME/bin/java"
else
    sudo apt update
    sudo apt install openjdk-8-jdk -y
fi

if [[ "$_java" ]]; then
    echo "Java8 has been successfully installed!"
fi
wget https://ci.opencollab.dev/job/NukkitX/job/Nukkit/job/master/lastSuccessfulBuild/artifact/target/nukkit-1.0-SNAPSHOT.jar
echo "Starting the Nukkit Server..."
if [ -f "nukkit-1.0-SNAPSHOT.jar" ]; then
    java -jar nukkit-1.0-SNAPSHOT.jar
else
    echo "The file could not be uploaded nukkit-1.0-SNAPSHOT.jar"
fi

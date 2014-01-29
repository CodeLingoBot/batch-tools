ROOT=DSPACETOOLSROOT
DSROOT=DSPACEROOT
HPFX=YOURPFX
SOLR=SOLRROOT

USERNAME=$1
shift
BATCH=`date +"%Y-%m-%d_%H:%M:%S"`
FNAME=job.${BATCH}-${USERNAME}.$1
QDIR=${ROOT}/queue/
RUNNING=${QDIR}/${FNAME}.running.txt
COMPLETE=${QDIR}/${FNAME}.complete.txt

VER=3

RUN=false

if [ "$1" = "filter-media" ]
then
  echo Command: "$@" > ${RUNNING}
  ${DSROOT}/bin/dspace "$@" >> ${RUNNING} 2>&1 
  
  REINDEX=1
  while [ $# -ge 1 ]
  do 
    x=$1
    shift
    
    if [ "$x" = "-n" ]
    then
      REINDEX=0
    fi
  done
  
  if [ $REINDEX = 1 ]
  then
    if [ $VER = 3 ]
    then
      echo "${DSROOT}/bin/dspace update-discovery-index" >> ${RUNNING} 2>&1 
      ${DSROOT}/bin/dspace update-discovery-index >> ${RUNNING} 2>&1 
    
      echo "${DSROOT}/bin/dspace oai import" >> ${RUNNING} 2>&1
      ${DSROOT}/bin/dspace oai import >> ${RUNNING} 2>&1

    fi 
  fi

  mv ${RUNNING} ${COMPLETE}
elif [ "$1" = "metadata-import" ]
then
  echo Command: "$@" > ${RUNNING}
  ${DSROOT}/bin/dspace "$@" >> ${RUNNING} 2>&1 

  while [ $# -ge 1 ]
  do 
    x=$1
    shift
    
    if [ "$x" = "-s" ]
    then
      if [ $VER = 3 ]
      then
        echo "${DSROOT}/bin/dspace update-discovery-index" >> ${RUNNING} 2>&1 
        ${DSROOT}/bin/dspace update-discovery-index >> ${RUNNING} 2>&1 
      
      echo "${DSROOT}/bin/dspace oai import" >> ${RUNNING} 2>&1
      ${DSROOT}/bin/dspace oai import >> ${RUNNING} 2>&1

      fi

    fi
  done

  mv ${RUNNING} ${COMPLETE}
elif [ "$1" = "gu-refresh-statistics" ]
then
  echo Command: "$@" > ${RUNNING}
  echo ${DSROOT}/bin/dspace stat-general >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-general >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stat-report-general >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-report-general >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stat-monthly >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-monthly >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stat-report-monthly >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stat-report-monthly >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace stats-util -o >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace stats-util -o >> ${RUNNING} 2>&1 

  if [ $VER = 3 ]
  then
    ${DSROOT}/bin/dspace update-discovery-index -o >> ${RUNNING} 2>&1 
  fi

  mv ${RUNNING} ${COMPLETE}
elif [ "$1" = "gu-change-parent" ]
then
  echo Command: "$@" > ${RUNNING}
  echo ${DSROOT}/bin/dspace community-filiator -r -c $2 -p $3 >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace community-filiator -r -c $2 -p $3 >> ${RUNNING} 2>&1 

  echo ${DSROOT}/bin/dspace community-filiator -s -c $2 -p $4 >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace community-filiator -s -c $2 -p $4 >> ${RUNNING} 2>&1 

  mv ${RUNNING} ${COMPLETE}
elif [ "$1" = "gu-change-coll-parent" ]
then
  echo Command: "$@" > ${RUNNING}
  echo "Updating database..." >> ${RUNNING} 2>&1 
  /usr/bin/psql -c "update community2collection set community_id=$4 where community_id=$3 and collection_id=$2;" >> ${RUNNING} 2>&1
  echo " ** The item has been moved, but the search index does not yet reflect the change" >> ${RUNNING} 2>&1 
  echo " ** You must run index-init while the server is offline" >> ${RUNNING} 2>&1 
  echo " ** You must run update-discovery-index -f after restarting the server" >> ${RUNNING} 2>&1 
  echo " ** You must then run oai import" >> ${RUNNING} 2>&1 
  mv ${RUNNING} ${COMPLETE}
elif [ "$1" = "gu-ingest" ]
then 
  USER=$2
  COLL=$3
  LOC=$4
  MAP=$5
  
  echo Command: "$@" > ${RUNNING}
  echo Command: import -a -e $USER -c $COLL -s $LOC -m $MAP >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace import -a -e $USER -c $COLL -s $LOC -m $MAP >> ${RUNNING} 2>&1 

  echo "Modify Map File : ${MAP}" >> ${RUNNING} 
  sed -e "s/ /_/g" -i $MAP >> ${RUNNING} 2>&1 
  sed -e "s|_\(${HPFX}\.\d+/\)| \1|" -i $MAP >> ${RUNNING} 2>&1 
  sed -e "s|_\(${HPFX}/\)| \1|" -i $MAP >> ${RUNNING} 2>&1 

  echo "${DSROOT}/bin/dspace filter-media -p 'Scribd Upload' -f -n -v -i $COLL" >> ${RUNNING} 
  ${DSROOT}/bin/dspace filter-media -p "Scribd Upload" -f -n -v -i $COLL >> ${RUNNING} 2>&1 
     
  echo "${DSROOT}/bin/dspace filter-media -i $COLL" >> ${RUNNING} 
  ${DSROOT}/bin/dspace filter-media -i $COLL >> ${RUNNING} 2>&1 
        
  if [ $VER = 3 ]
  then
    echo "${DSROOT}/bin/dspace update-discovery-index" >> ${RUNNING} 2>&1 
    ${DSROOT}/bin/dspace update-discovery-index >> ${RUNNING} 2>&1 
      
    echo "${DSROOT}/bin/dspace oai import" >> ${RUNNING} 2>&1
    ${DSROOT}/bin/dspace oai import >> ${RUNNING} 2>&1
  fi
  
  echo "Job complete" >> ${RUNNING} 2>&1
  date >> ${RUNNING} 2>&1

  mv ${RUNNING} ${COMPLETE}
elif [ "$1" = "gu-uningest" ]
then 
  USER=$2
  MAP=$3
  
  echo Command: "$@" > ${RUNNING}
  echo Command: import -d -e ${USER} -m "${MAP}" >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace import -d -e ${USER} -m "${MAP}" >> ${RUNNING} 2>&1 

  mv ${RUNNING} ${COMPLETE}
elif [ "$1" = "gu-reindex" ]
then 
  SRCH=$2
  VAL=$3
  
  echo Command: "$@" > ${RUNNING}

  echo Command: curl "${SOLR}/search/update?stream.body=&lt;update&gt;&lt;delete&gt;&lt;query&gt;location.${SRCH}:${VAL}&lt;/query&gt;&lt;/delete&gt;&lt;commit/&gt;&lt;/update&gt;" >> ${RUNNING} 2>&1 
  curl "${SOLR}/search/update?stream.body=<update><delete><query>location.${SRCH}:${VAL}</query></delete><commit/></update>" >> ${RUNNING} 2>&1 

  echo "${DSROOT}/bin/dspace update-discovery-index" >> ${RUNNING} 2>&1 
  ${DSROOT}/bin/dspace update-discovery-index >> ${RUNNING} 2>&1 

  mv ${RUNNING} ${COMPLETE}
else
  echo "Unsupported DSpace Command"
fi


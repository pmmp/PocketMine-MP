#if(${TYPE_HINT} != ${RETURN_TYPE} && ${TYPE_HINT} != "")/** @return ${TYPE_HINT} */#end
public ${STATIC} function ${GET_OR_IS}${NAME}()#if(${RETURN_TYPE}): ${RETURN_TYPE}#else#end
{ return #if(${STATIC} == "static")self::$${FIELD_NAME};#else$this->${FIELD_NAME};#end }

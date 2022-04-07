public ${STATIC} function set${NAME}(#if (${SCALAR_TYPE_HINT})${SCALAR_TYPE_HINT} #end$${PARAM_NAME})#if (${VOID_RETURN_TYPE}):void #end
{#if (${STATIC} == "static") self::$${FIELD_NAME} = $${PARAM_NAME}; #else $this->${FIELD_NAME} = $${PARAM_NAME}; #end}

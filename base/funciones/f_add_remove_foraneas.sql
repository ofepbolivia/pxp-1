CREATE OR REPLACE FUNCTION pxp.f_add_remove_foraneas (
  p_tabla varchar,
  p_esquema varchar,
  p_operacion varchar
)
RETURNS boolean AS
$body$
/*
Author: RAC
Date: 24-1-2013
Description: Remover llaves foraneas de la tabla indicada
*/
DECLARE

   g_registros record;
   v_consulta varchar;

BEGIN


   IF p_operacion = 'eliminar' THEN
   
        FOR g_registros in (Select 
                            n.nspname,
                            c.relname,
                            t.conname,
                            pg_catalog.pg_get_constraintdef(t.oid) as definicion
                                from pg_namespace n 
                                inner join  pg_class c on(n.oid=c.relnamespace)  
                                inner join pg_constraint t on (c.oid=t.conrelid)
                                where  pxp.f_campo_constraint(pg_catalog.pg_get_constraintdef(t.oid),0) in
                                (select rel.relname from pg_namespace esq
                                inner join pg_class rel on(rel.relnamespace=esq.oid)
                                where esq.nspname=p_esquema and c.relname=p_tabla) and t.contype like 'f' 
                                order by c.relname) LOOP
                                
                     --si tiene datos para la tabla los elimina  
                     --para realizar un BK 
                      DELETE FROM 
                      pxp.tforenkey 
                      WHERE 
                      tabla = p_esquema||'.'||p_tabla;
                     
               
                      INSERT INTO 
                        pxp.tforenkey
                      (
                        tabla,
                        obs,
                        llave
                      ) 
                      VALUES (
                        p_esquema||'.'||p_tabla,
                        g_registros.definicion,
                        g_registros.conname
                      );
                      
                  --elimina las llaves foraneas de la tabla
                  
             v_consulta='ALTER TABLE '||p_esquema||'.'||p_tabla||'    
  		                DROP CONSTRAINT "'||g_registros.conname||'" RESTRICT';  
                        
             execute (v_consulta);  


	  END LOOP;
    
    ELSEIF p_operacion = 'insertar' THEN
    
   
       FOR g_registros in (select * 
                            from pxp.tforenkey f 
                            where f.tabla = p_esquema||'.'||p_tabla )   LOOP
        
       
        
          v_consulta='ALTER TABLE '||p_esquema||'.'||p_tabla ||' ADD CONSTRAINT '||g_registros.llave||' '||g_registros.obs;
		
          execute (v_consulta);
        
        END LOOP;
        
        
        
    
    ELSE
    
      raise exception 'operacion desconocida %  son validos (insertar, eliminar)',p_operacion;
    
    END IF;
    
    
    
    RETURN TRUE;
    
EXCEPTION

WHEN OTHERS THEN
				
	RETURN FALSE;
    

END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY INVOKER
COST 100;
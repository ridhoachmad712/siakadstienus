<?php
// Read-only master listing pagination. Call only after the endpoint role guard.
function siakad_tabel_halaman($db,$sql,$fields=[]) {
    $size=(int)($_POST['size']??15); if(!in_array($size,[15,25,50],true))$size=15;
    $page=max(1,(int)($_POST['page']??1));
    $base=preg_replace('/\s+ORDER BY\s+.+$/is','',$sql);
    $parts=preg_split('/\s+WHERE\s+/i',$base,2);
    if(count($parts)===2)$base=$parts[0].' WHERE ('.$parts[1].')';
    $filters=[];
    foreach($fields as $label=>$field){
        $from=preg_replace('/^SELECT\s+.*?\s+FROM\s+/is','FROM ',$base);
        $options=siakad_semua($db,'SELECT DISTINCT '.$field.' AS value '.$from.' ORDER BY value');
        $filters[$label]=array_values(array_filter(array_column($options,'value'),fn($v)=>$v!==null && $v!==''));
    }
    foreach($fields as $label=>$field){
        $value=(string)($_POST['filters'][$label]??'');
        if($value!=='')$base.=(stripos($base,' WHERE ')===false?' WHERE ':' AND ').$field."='".mysqli_real_escape_string($db,$value)."'";
    }
    $countSql=preg_replace('/^SELECT\s+.*?\s+FROM\s+/is','SELECT COUNT(*) AS total FROM ',$base);
    $total=(int)(siakad_baris($db,$countSql)['total']??0);
    $page=min($page,max(1,(int)ceil($total/$size)));$offset=($page-1)*$size;
    // All legacy endpoints order their unfiltered query; also keep search results deterministic.
    $order=preg_match('/\s+ORDER BY\s+(.+)$/is',$sql,$matches)?' ORDER BY '.$matches[1]:' ORDER BY 1';
    echo '<div class="sk-server-results" data-total="'.$total.'" data-page="'.$page.'" data-size="'.$size.'" data-filters="'.htmlspecialchars(json_encode($filters),ENT_QUOTES,'UTF-8').'">';
    return [$base.$order.' LIMIT '.$size.' OFFSET '.$offset,$offset];
}

function siakad_tabel_statis($db,$sql,$searchFields=[]) {
    $size=(int)($_GET['size']??15);if(!in_array($size,[15,25,50],true))$size=15;
    $page=max(1,(int)($_GET['page']??1));
    $base=preg_replace('/\s+ORDER BY\s+.+$/is','',$sql);
    $parts=preg_split('/\s+WHERE\s+/i',$base,2);if(count($parts)===2)$base=$parts[0].' WHERE ('.$parts[1].')';
    $search=is_string($_GET['list_search']??null)?$_GET['list_search']:'';
    if($search!=='' && $searchFields){$conditions=[];foreach($searchFields as $field)$conditions[]=$field." LIKE '%".mysqli_real_escape_string($db,$search)."%'";$base.=(stripos($base,' WHERE ')===false?' WHERE ':' AND ').'('.implode(' OR ',$conditions).')';}
    $total=(int)(siakad_baris($db,preg_replace('/^SELECT\s+.*?\s+FROM\s+/is','SELECT COUNT(*) AS total FROM ',$base))['total']??0);
    $page=min($page,max(1,(int)ceil($total/$size)));$offset=($page-1)*$size;
    $GLOBALS['sk_static_pager']=['page'=>$page,'size'=>$size,'total'=>$total,'search'=>$search];
    $order=preg_match('/\s+ORDER BY\s+(.+)$/is',$sql,$matches)?' ORDER BY '.$matches[1]:' ORDER BY 1';
    return [$base.$order.' LIMIT '.$size.' OFFSET '.$offset,$offset];
}

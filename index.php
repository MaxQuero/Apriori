<pre>
<?php

function array_cartesian($items) {
    $result  = array();
    $num     = count($items);
    $members = pow(2, $num);
    for($i=0; $i<$members; $i++)
    {
        $b   = sprintf("%0".$num."b", $i);
        $tmp = array();
        for($j=0; $j<$num; $j++)
        {
            if($b[$j]=='1')
            {
                $tmp[] = $items[$j];
            }
        }

        if($tmp)
        {
            sort($tmp);
            $result[] = $tmp;
        }
    }

    return $result;
}
$data = array(
    array('apples', 'pears',  'oranges'),
    array('steve', 'bob')
);
$data = array(
    array('L1', 'L2', 'L3'),
    array('L1', 'L2', 'L5')
);

$res_matrix = array_cartesian( $data );


function array_cartesian_product( $arrays )
{
    $result = array();
    $arrays = array_values( $arrays );

    $sizeIn = sizeof( $arrays );
    $size = $sizeIn > 0 ? 1 : 0;
    foreach ($arrays as $array)
        $size = $size * sizeof( $array );
    $res_index = 0;
    for ( $i = 0; $i < $size; $i++ )
    {
        $is_duplicate = false;
        $curr_values  = array();
        for ( $j = 0; $j < $sizeIn; $j++ )
        {
            $curr = current( $arrays[$j] );
            if ( !in_array( $curr, $curr_values ) )
            {
                array_push( $curr_values , $curr );
            }
            else
            {
                $is_duplicate = true;
                break;
            }
        }
        if ( !$is_duplicate )
        {
            $result[ $res_index ] = $curr_values;
            $res_index++;
        }
        for ( $j = ( $sizeIn -1 ); $j >= 0; $j-- )
        {
            $next = next( $arrays[ $j ] );
            if ( $next )
            {
                break;
            }
            elseif ( isset ( $arrays[ $j ] ) )
            {
                reset( $arrays[ $j ] );
            }
        }
    }
    return $result;
}
$datz = array(
    array('L1', 'L2'),
    array('L1', 'L3'),
    array('L1', 'L5'),
    array('L2', 'L3'),
    array('L2', 'L4'),
    array('L2', 'L5')
);

print_r(array_cartesian_product($data));
$bdd = new PDO('mysql:host=localhost;dbname=apriori;charset=utf8', 'root', '');


$req_nom_prod = $bdd->query('SELECT * FROM comporte INNER JOIN produits WHERE id_panier=1 AND comporte.id_produit=produits.id_produit;');

while ($donnees = $req_nom_prod->fetch()) {
    print_r(array($donnees['nom_produit']));
}




//Il faut récupérer le nombre total de produit
$nbproduitsrep = $bdd->query('SELECT count(*) FROM produits');
$nbprod = $nbproduitsrep->fetch()[0];
echo $nbprod;

for($i=1; $i<$nbprod+1;$i++){
    $reqSupport = $bdd->query('SELECT count(*) FROM comporte WHERE id_produit='.$i.';');
    $req_nom_prod = $bdd->query('SELECT nom_produit FROM produits WHERE id_produit='.$i.';');
    $support = $reqSupport->fetch()[0];
    $nom_prod = $req_nom_prod->fetch()[0];
    $tabSupport[] = array($nom_prod=>$support);
    $req_nom_prod->closeCursor(); // Termine le traitement de la requête
    $reqSupport->closeCursor();

}
print_r($tabSupport);



$req_nom_prod->closeCursor(); // Termine le traitement de la requête
$reqSupport->closeCursor();



/*$tabItems = array(1,2,3,4);

public function getOccurenceCheckout(){

}
public function setMinSupport($occurence, $tabCombinaisonsProduits){
        for($i=0; $i<sizeof($tabItems);$i++){

        }
}*/
?></pre>

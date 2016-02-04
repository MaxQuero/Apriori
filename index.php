<?php
$bdd = new PDO('mysql:host=localhost;dbname=apriori;charset=utf8', 'root', '');

//Il faut récupérer le nombre total de produit
$nbproduitsrep = $bdd->query('SELECT count(*) FROM produits');
$nbprod = $nbproduitsrep->fetch()[0];
echo $nbprod;

// retourne tab qui contient key = nom_prod et value = support(occurence)
for($i=1; $i<$nbprod+1;$i++){
    $reqSupport = $bdd->query('SELECT count(*) FROM comporte WHERE id_produit='.$i.';');
    $req_nom_prod = $bdd->query('SELECT nom_produit FROM produits WHERE id_produit='.$i.';');
    $support = $reqSupport->fetch()[0];
    $nom_prod = $req_nom_prod->fetch()[0];
    $tabSupport[] = array($nom_prod=>$support);
    $req_nom_prod->closeCursor(); // Termine le traitement de la requête
    $reqSupport->closeCursor();

}
var_dump($tabSupport);



$req_nom_prod->closeCursor(); // Termine le traitement de la requête
$reqSupport->closeCursor();



/*$tabItems = array(1,2,3,4);

public function getOccurenceCheckout(){

}
public function setMinSupport($occurence, $tabCombinaisonsProduits){
        for($i=0; $i<sizeof($tabItems);$i++){

        }
}*/
?>
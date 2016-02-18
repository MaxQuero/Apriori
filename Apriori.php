<pre>
<?php
/**
 * Apriori Algorithm - ???????? ???????
 * PHP Version 5.0.0
 * Version 0.1 Beta
 * @link http://vtwo.org
 * @author VTwo Group (info@vtwo.org)
 * @license GNU GENERAL PUBLIC LICENSE
 *
 *
 * '-)
 */
class Apriori {
    private $delimiter   = ',';
    private $minSup      = 2;
    private $minConf     = 75;

    private $rules       = array();
    private $table       = array();
    private $allthings   = array();
    private $allsups     = array();
    private $keys        = array();
    private $freqItmsts  = array();
    private $phase       = 1;

    //maxPhase>=2
    private $maxPhase    = 20;

    private $fiTime      = 0;
    private $arTime      = 0;

    public function setDelimiter($char)
    {
        $this->delimiter = $char;
    }

    public function setMinSup($int)
    {
        $this->minSup = $int;
    }

    public function setMinConf($int)
    {
        $this->minConf = $int;
    }

    public function setMaxScan($int)
    {
        $this->maxPhase = $int;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function getMinSup()
    {
        return $this->minSup;
    }

    public function getMinConf()
    {
        return $this->minConf;
    }

    public function getMaxScan()
    {
        return $this->maxPhase;
    }



    private function getDatabaseData(){
        $bdd = new PDO('mysql:host=localhost;dbname=apriori;charset=utf8', 'root', '');

//Il faut r�cup�rer le nombre total de produit

        $nbpaniersrep = $bdd->query('SELECT count(*) FROM paniers');
        $nbpan = $nbpaniersrep->fetch()[0];

           // $reqSupport = $bdd->query('SELECT count(*) FROM comporte WHERE id_produit='.$i.';');
        for($i=1; $i<$nbpan+1;$i++) {
            $req_nom_prod = $bdd->query('SELECT * FROM comporte INNER JOIN produits WHERE id_panier='.$i.' AND comporte.id_produit=produits.id_produit;');
            while ($donnees = $req_nom_prod->fetch()) {
                $tabSupport[$i][] =  $donnees['nom_produit'];

            }
        }
        $req_nom_prod->closeCursor(); // Termine le traitement de la requ�te

        return $tabSupport;


    }

    private function setDataToFile($tabData)
    {
        $file    = fopen( "fichier.txt", "w" );

        foreach($tabData as $key=>$value){
            foreach($value as $keyProduct=>$nameProduct){

                fwrite($file,$nameProduct.', ');
            }
            fwrite($file, "\n");
        }
        fclose($file);
    }




    /**
    1. ???? ?????? ?? ?? ????
    2. ???? ?????? ?? ?? ???? ?? ????? ?? ???
    3. ????? ?????? ? ????? ???? ?? ?????? ?? ??? - ??? 1
    ????: ????? ????? ?????? ?????
     **/

    private function makeTableBase($db)
    {
        $table   = array();
        $array   = array();
        $counter = 1;

        if(!is_array($db))
        {
            $db = file($db);
        }

        $num = count($db);
        for($i=0; $i<$num; $i++)
        {
            $tmp  = explode($this->delimiter, $db[$i]);
            $num1 = count($tmp);
            $x    = array();
            for($j=0; $j<$num1; $j++)
            {
                $x = trim($tmp[$j]);
                if($x==='')
                {
                    continue;
                }

                if(!isset($this->keys['v->k'][$x]))
                {
                    $this->keys['v->k'][$x]         = $counter;
                    $this->keys['k->v'][$counter]   = $x;
                    $counter++;
                }

                if(!isset($array[$this->keys['v->k'][$x]]))
                {
                    $array[$this->keys['v->k'][$x]] = 1;
                    $this->allsups[$this->keys['v->k'][$x]] = 1;
                }
                else
                {
                    $array[$this->keys['v->k'][$x]]++;
                    $this->allsups[$this->keys['v->k'][$x]]++;
                }

                $table[$i][$this->keys['v->k'][$x]] = 1;
            }
        }

        $tmp = array();
        foreach($array as $item => $sup)
        {
            if($sup>=$this->minSup)
            {

                $tmp[] = array($item);
            }
        }

        $this->allthings[$this->phase] = $tmp;
        $this->table = $table;
    }



    private function makeTable($db)
    {  $table   = array();
        $array   = array();
        $counter = 1;

        if(!is_array($db))
        {
            $db = file($db);
        }

        $num = count($db);

        for($i=0; $i<$num; $i++)
        {
            $tmp  = explode($this->delimiter, $db[$i]);


            $num1 = count($tmp);
            $x    = array();
            for($j=0; $j<$num1; $j++)
            {
                //nom_produit
                $x = trim($tmp[$j]);

                if($x==='')
                {
                    continue;
                }
                //s'il existe un �l�ment
                if(!isset($this->keys['v->k'][$x]))
                {
                    //keys = tab de deux sous tableaux :
                    // [v->k] : [�l�ment] => id et
                    // [k->v]: [id]=> �lement
                    $this->keys['v->k'][$x]         = $counter;
                    $this->keys['k->v'][$counter]   = $x;
                    $counter++;
                }
                //Si le couple array[contenude[v->k]] (=si array[cl� element]) ne contient pas une valeur
                if(!isset($array[$this->keys['v->k'][$x]]))
                {
                    //On l'initialise � 1
                    $array[$this->keys['v->k'][$x]] = 1;
                    $this->allsups[$this->keys['v->k'][$x]] = 1;
                }
                else
                {
                    //Sinon on incr�mente sa valeur (le nombre d'occurence de l'�l�ment)
                    $array[$this->keys['v->k'][$x]]++;
                    //allups contiendra �galement le nombre d'occurence de chaque [cl� �l�ment] (= id_element)
                    $this->allsups[$this->keys['v->k'][$x]]++;
                }
                //contient, pour chaque �l�m�nt de cahque  panier (chaque ligne) la valeur 1
                $table[$i][$this->keys['v->k'][$x]] = 1;

            }

        }

        $tmp = array();
        //On ne garde que les id_�l�ments de ceux dont le support est > minSupport
        foreach($array as $item => $sup)
        {

            if($sup>=$this->minSup)
            {
                $tmp[] = array($item);
            }

        }
        //Tableau contenant en value l'id_element de ceux dont le support > minSupport
        $this->allthings[$this->phase] = $tmp;
        //contient, pour chaque �l�m�nt de cahque  panier (chaque ligne) la valeur 1
        $this->table = $table;

    }

    //retourne les supports de TOUS les �l�ments ET combinaisons d'�l�ments
    private function scan($arr, $implodeArr = '')
    {
        $cr = 0;
        if($implodeArr)
        {
            if(isset($this->allsups[$implodeArr]))
            {
                return $this->allsups[$implodeArr];
            }
        }
        else
        {
            sort($arr);
            $implodeArr = implode($this->delimiter, $arr);
            if(isset($this->allsups[$implodeArr]))
            {
                return $this->allsups[$implodeArr];
            }
        }
        $num  = count($this->table);
        $num1 = count($arr);
        for($i=0; $i<$num; $i++)
        {
            $bool = true;
            for($j=0; $j<$num1; $j++)
            {
                if(!isset($this->table[$i][$arr[$j]]))
                {
                    $bool = false;
                    break;
                }
            }

            if($bool)
            {
                $cr++;
            }
        }

        $this->allsups[$implodeArr] = $cr;
        return $cr;

    }

    /// Ressort toutes les combinaisons possibles entre $arr et $arr2 (avec supprot >minSupport, car �a aura �t� tri� avant)
    private function combine($arr1, $arr2)
    {
        $result = array();

        $num  = count($arr1);
        $num1 = count($arr2);
        for($i=0; $i<$num; $i++)
        {
            if(!isset($result['k'][$arr1[$i]]))
            {
                $result['v'][] = $arr1[$i];
                $result['k'][$arr1[$i]] = 1;
            }
        }

        for($i=0; $i<$num1; $i++)
        {
            if(!isset($result['k'][$arr2[$i]]))
            {
                // donne le tab [key]=>id_element
                $result['v'][] = $arr2[$i];
                //donne le tab [id_elementt]=>1
                $result['k'][$arr2[$i]] = 1;
            }
        }
        return $result['v'];
    }

    /**
    1. ??? ???? ?? ?? ???? ?? ????? ???? ?? ?????? ?? ?? ??????
    {1,2,3,4} => {A,B,C,D}
     **/
    private function realName($arr)
    {
        $result = '';

        $num = count($arr);
        for($j=0; $j<$num; $j++)
        {
            if($j)
            {
                $result .= $this->delimiter;
            }

            $result .= $this->keys['k->v'][$arr[$j]];
        }

        return $result;
    }

    //1-2=>2-3 : false
    //1-2=>5-6 : true
    private function checkRule($a, $b)
    {
        $a_num = count($a);
        $b_num = count($b);
        for($i=0; $i<$a_num; $i++)
        {
            for($j=0; $j<$b_num; $j++)
            {
                if($a[$i]==$b[$j])
                {
                    return false;
                }
            }
        }

        return true;
    }

    private function confidence($sup_a, $sup_ab)
    {
        return round(($sup_ab / $sup_a) * 100, 2);
    }

    private function subsets($items)
    {
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

    /**
    1. ???? ????? ?????? ?? ?? ?? ??????
     **/
    private function freqItemsets($db)
    {
        $this->fiTime = $this->startTimer();

        $this->makeTable($db);
        while(1)
        {
            if($this->phase>=$this->maxPhase)
            {
                break;
            }
            $num = count($this->allthings[$this->phase]);
            $cr  = 0;
            for($i=0; $i<$num; $i++)
            {
                for($j=$i; $j<$num; $j++)
                {
                    if($i==$j)
                    {
                        continue;
                    }
                    //Sors toutes les combinaisons des �l�m�nts dont support > minSUpport (car allthings contient que ces �l�ments)
                    $item = $this->combine($this->allthings[$this->phase][$i], $this->allthings[$this->phase][$j]);
                    sort($item);

                    $implodeArr = implode($this->delimiter, $item);
                    if(!isset($this->freqItmsts[$implodeArr]))
                    {
                        $sup = $this->scan($item, $implodeArr);
                        if($sup>=$this->minSup)
                        {
                            $this->allthings[$this->phase+1][] = $item;
                            $this->freqItmsts[$implodeArr] = 1;
                            $cr++;
                        }
                    }
                }
            }

            if($cr<=1)
            {
                break;
            }

            $this->phase++;
        }

        //Pour chaque freqItmsts, on supprime les "sous sous �l�ments"(sauf celui repr�sentant le sous �l�ment lui-m�me)
        //de chaque sous �l�ments,
        // si le sous �l�ment est compos� de 3 �l�ments ou plus
        foreach($this->freqItmsts as $k => $v)
        {
            //on cr�� $arr = un sous-ensemble(ex : L1, L2, L5 =3) -> [0]=>L1, [1]=>L2, [2]=>L3
            $arr = explode($this->delimiter, $k);
            $maxNum=0;
            //Nombre d'�l�ment dans le sous ensemble
            $num = count($arr);
            //Si le nombre de sous-�l�ments > max ce nombre devient nouveau max
            if($num > $maxNum){
                $maxNum = $num;
            }

            //Si l'ensemble trait� est >= 3 �l�ments (ex : {L1, L2, L5} =3)
            if($num>=3)
            {
                //Affiche les sous-sousensemble possible du tableau pass� en param�tre
                //ex : {L1, L2, L5} = {L1}, {L1,L2}...
                $subsets = $this->subsets($arr);
                $num1    = count($subsets);
                //Pour chaque sous_ensemble de l'ensemble $arr
                for($i=0; $i<$num1; $i++)
                {
                    // nombre d'�lements dans le sosu-ensemble $i
                    //S'il est inf�rieur � num
                    if(count($subsets[$i])<$num)
                    {
                        //on enleve le sous �l�ment (ex pour L1, L2, L3 on ne gardera que ce
                    // sous sous ensemble, et non pas L1; L1,L2 ; L1,L3...
                        unset($this->freqItmsts[implode($this->delimiter, $subsets[$i])]);
                       // print_r($this->freqItmsts);
                       // var_dump('fin');


                    }
                    else
                    {
                        break;
                    }
                }

            }

        }

        $this->deleteDeprecatedItems($maxNum);

        $this->fiTime = $this->stopTimer($this->fiTime);
    }

    //Si le numMax pass� en param�tre >= 3, c'est qu'on va �tre dans un ensemble >=3-elements
    //On supprimera donc les support de 2-elements restant qui n'auront pas �t� unset avant
    private function deleteDeprecatedItems($numMax){
        if ($numMax >= 3) {
            foreach($this->freqItmsts as $k => $v){
                // On r�cup�re un tab de sous ensemble
                $arr = explode($this->delimiter, $k);
                //On compte le nombre d'�l�ment du sous ensemble
                $num = count($arr);
                if($num<3){
                    // SI ce sous ensemble est compos� de moins de 3 �l�m�ents on l'unset
                    unset($this->freqItmsts[implode($this->delimiter, $arr)]);
                }

            }
        }
    }
    //Fonction principale
    public function process($db)
    {

        $tabData=$this->getDatabaseData();
        $checked = $result = array();
        $this->setDataToFile($tabData);

        $this->freqItemsets($db);

        //Partie sur la confiance et les r�gles d'association
        $this->arTime = $this->startTimer();

        foreach($this->freqItmsts as $k => $v)
        {


            $arr     = explode($this->delimiter, $k);
            $subsets = $this->subsets($arr);
            $num     = count($subsets);

            for($i=0; $i<$num; $i++)
            {
                for($j=0; $j<$num; $j++)
                {

                    if($this->checkRule($subsets[$i], $subsets[$j]))
                    {
                        $n1 = $this->realName($subsets[$i]);
                        $n2 = $this->realName($subsets[$j]);

                        $scan = $this->scan($this->combine($subsets[$i], $subsets[$j]));

                        $c1   = $this->confidence($this->scan($subsets[$i]), $scan);
                        $c2   = $this->confidence($this->scan($subsets[$j]), $scan);

                        if($c1>=$this->minConf)
                        {
                            $result[$n1][$n2] = $c1;
                        }

                        if($c2>=$this->minConf)
                        {
                            $result[$n2][$n1] = $c2;
                        }

                        $checked[$n1.$this->delimiter.$n2] = 1;
                        $checked[$n2.$this->delimiter.$n1] = 1;

                    }
                }
            }
        }
        $this->arTime = $this->stopTimer($this->arTime);

        return $this->rules = $result;
    }

    public function printFreqItemsets()
    {
        echo 'Time: '.$this->fiTime.' second(s)<br />===============================================================================<br />';

        foreach($this->freqItmsts as $k => $v)
        {
            $tmp  = '';
            $tmp1 = '';
            $k    = explode($this->delimiter, $k);
            $num  = count($k);
            for($i=0; $i<$num; $i++)
            {
                if($i)
                {
                    $tmp  .= $this->delimiter.$this->realName($k[$i]);
                    $tmp1 .= $this->delimiter.$k[$i];
                }
                else
                {
                    $tmp  = $this->realName($k[$i]);
                    $tmp1 = $k[$i];
                }
            }

            echo '{'.$tmp.'} = '.$this->allsups[$tmp1].'<br />';
        }
    }

    public function saveFreqItemsets($filename)
    {
        $content = '';

        foreach($this->freqItmsts as $k => $v)
        {
            $tmp  = '';
            $tmp1 = '';
            $k    = explode($this->delimiter, $k);
            $num  = count($k);
            for($i=0; $i<$num; $i++)
            {
                if($i)
                {
                    $tmp  .= $this->delimiter.$this->realName($k[$i]);
                    $tmp1 .= $this->delimiter.$k[$i];
                }
                else
                {
                    $tmp  = $this->realName($k[$i]);
                    $tmp1 = $k[$i];
                }
            }

            $content .= '{'.$tmp.'} = '.$this->allsups[$tmp1]."\n";
        }

        file_put_contents($filename, $content);
    }

    public function getFreqItemsets()
    {
        $result = array();

        foreach($this->freqItmsts as $k => $v)
        {
            $tmp        = array();
            $tmp['sup'] = $this->allsups[$k];
            $k          = explode($this->delimiter, $k);
            $num        = count($k);
            for($i=0; $i<$num; $i++)
            {
                $tmp[] = $this->realName($k[$i]);
            }

            $result[] = $tmp;
        }

        return $result;
    }

    public function printAssociationRules()
    {
        echo 'Time: '.$this->arTime.' second(s)<br />===============================================================================<br />';

        foreach($this->rules as $a => $arr)
        {
            foreach($arr as $b => $conf)
            {
                echo "$a => $b = $conf%<br />";
            }
        }
    }

    public function saveAssociationRules($filename)
    {
        $content = '';

        foreach($this->rules as $a => $arr)
        {
            foreach($arr as $b => $conf)
            {
                $content .= "$a => $b = $conf%\n";
            }
        }

        file_put_contents($filename, $content);
    }

    public function getAssociationRules()
    {
        return $this->rules;
    }

    private function startTimer()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    private function stopTimer($start, $round=2)
    {
        $endtime = $this->startTimer()-$start;
        $round   = pow(10, $round);
        return round($endtime*$round)/$round;
    }

}
$Apriori = new Apriori();


?>
</pre>
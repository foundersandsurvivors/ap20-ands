<?php

/**
* Example usage for the Open Calais Tags class written by Dan Grossman
* (http://www.dangrossman.info). Read about this class and how to get
* an API key at http://www.dangrossman.info/open-calais-tags
*/

require('../x/opencalais.php');

$apikey = 'c8ga67e3bq4t7ssb7hjsve7h';

$con[0] = <<<EOD_1
The Mercury (Hobart, Tas. : 1860 - 1954) Saturday 22 February 1862 p 2
MAINTENANCE. - Amelia Woodhall summoned John Woodhall, her husband, for leaving her without means of support.
Defendant said he had a house and five acres of land, and was willing to receive his wife and children.
Mr. Lees, for the complainant, said defendant went away eleven months ago with a young woman and had since brought her to live in the immediate neighborhood ; he lived a life of idleness, and lived on the labor of his wife.
Defendant referred to the profits of his trade, and the stock of trees he had on his ground.
The Bench under the circumstances said they could not make any order.
Complainant then claimed the protection of the court against defendant's violence, and The Police Magistrate said that was a matter for another proceeding ; she had her remedy if she had any ground to fear his violence.
Complainant protested that she would not live with her husband again.
The Bench told her she might obtain protection for her earnings by resorting to the proper tribunal.
Mr. Lees intimated that this course would be adopted.
EOD_1;

$con[1] = <<<EOD_2
The Mercury (Hobart, Tas. : 1860 - 1954) Friday 9 January 1863 p 2
Assault.-Catherine Whittaker charged John Woodhall with assaulting her on 29th December last.
The complainant deposed that the defendant assaulted her near the door of her father's house.
Defendant at first commenced kicking up a row with witness's father. He then called witness names and struck her with his fist under the ear and knocked her down. Witness then threw a stone at him which hit him.
Cross-examined: Did not strike defendant first.
Richard Whittaker corroborated the complainant's evidence.
A little boy named Michael Whittaker was called but the Bench did not think it fit to receive his evidence.
The defendant called Thomas Coates who said that Whittaker and defendant had some words about a fence and then
the complainant hit defendant who then hit her and then she threw stones. The dispute began about a fence which Woodhall said was on his ground and he went to pull it down.
John Crowther was next called but knew nothing of the alleged assault.
The Bench dismissed the information, but cautioned defendant not to go on Whittakers premises again.
EOD_2;

$con[2] = <<<EOD_3
The Mercury (Hobart, Tas. : 1860 - 1954) Saturday 30 July 1864 p 2
WOODHALL v. WOODHALL..-An information by Amelia Woodhall against her husband for maintenance.
The complainant deposed that on the 28th of April an order was made upon the defendant by two justices of the peace for payment to her of 10s. per week for maintenance. The money was regularly paid up to seven weeks ago, when the
payments were discontinued. Witness was not aware of any sufficient reason for the non-payment. Hubert Day Church,Clerk of the Peace, produced the order of maintenance in question.
The Bench dismissed the complainant, on the ground that the order had lapsed.
EOD_3;

$con[3] = <<<EOD_4
<aifDescendant id_ns="ccc113961.01" naa_key="3037287">
  <surnameEnlistee>Askey</surnameEnlistee>
  <forenameEnlistee>Clarence Bernard Samuel</forenameEnlistee>
  <genealogy>
    <p>
      <l>Son of Samuel Askey (1863-1942) &amp; Sarah Louisa Gard (1862-1942),</l>
      <l>grandson of Robert Gard (1832-1916) &amp; Ann Phillips (1835-1891),</l>
      <l>great grandson of Henry Phillips (1795-1888) &amp; Mary Clark (1800-1863)</l>
    </p>
  </genealogy>
  <anyOtherDetails>Enlisted 9 Oct 1915. Discharged 15 Oct 1919.</anyOtherDetails>
  <otherSources>NAA Series B2455, Barcode 3037287.</otherSources>
</aifDescendant>
EOD_4;

$n = 0;
foreach ( $con as $content ) {

    echo "\n================= CONTENT[$n]:\n$content\n";
    $oc = new OpenCalais($apikey);
    $entities = $oc->getEntitiesSms($content);

    foreach ($entities as $type => $values) {

        echo "\n<entity-type>" . $type . "</entity-type>";
        echo "\n<ul>";

        foreach ($values as $entity) {
            echo "\n  <li>" . $entity . "</li>";
        }

        echo "\n</ul>\n\n";
}

}


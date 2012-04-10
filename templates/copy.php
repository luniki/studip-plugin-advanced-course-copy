<h1>COPIED!</h1>

<?
var_dump($_REQUEST);
?>
<table>
  <tr>
    <td>
      <?
         var_dump($src->toXML());
      ?>
    </td>

    <td style="vertical-align: top;">
      <?

         # TODO semester checken
         $semester = Request::option("semester");
         $modules = Request::getArray("modules");

         var_dump($copy->toXML());

      ?>
    </td>
  </tr>
</table>

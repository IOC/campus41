
**iedib-atto-snippet** és un plugin per l'editor atto del Moodle. Està basat amb el plugin  https://github.com/justinhunt/moodle-atto_snippet

S'ha modificat el plugin anterior per permetre templates més complexes a través de Handlebars. S'han creat una sèrie de helpers per aconseguir aquesta finalitat

### Helpers creats

- times: Crear bucles
- ifCond: Logica condicional
- math: Operacions matemàtiques amb variables

**times helper**
``` 
{{#times rows}}
   {{@index}} imprimeix l'index començant de 0
   {{@first}} variable que és TRUE si estam en el primer element
   {{@last}} variable que és TRUE si estam en el darrer element
{{/times}}
```

**ifCond helper**
``` 
{{#ifCond bar 'eq' 'foo'}}
  La variable bar és igual a foo
{{else}}
  La variable bar no és foo, en realitat és {{bar}}
{{/ifCond}}
```
En comptes de 'eq' es pot emprar, 'neq', 'gt', 'lt', 'geq', 'leq'.

**math helper**
``` 
La teva edat és {{age}}
L'any passat tenies {{math age '-' '1'}}
L'any que vé tendràs {{math age '+' '1'}}
El doble de la teva edat és {{math age '*' '2'}}
```

### Categories de plugins
Es possible classificar els botons dels snippets amb categories. En el nom de l'snippet es pot afegir una barra vertical seguit del nom de la categoria. Si el nom no conté aquesta part final, aleshores anirà a parar dins la categoria ALTRES.

Exemple de noms de plugins: Youtube|video  Vimeo|video   desplegable|bootstrap   taula


## Variables i valors per defecte

Els noms de les variables poden ésser qualssevol nom vàlid en javascript. Ara bé, si el nom de la variable comença amb $, s'estén que el valor d'aquesta variable es desarà dins el localStorage de l'explorador i, la propera vegada que s'empri, apareixerà el darrer valor utilitzat.

Els valors per defecte poden ésser simples noms
```
materia=MATEMÀTIQUES II
```
els quals es mostraran com un quadre de text,

una llista d'opcions
```
color=red|blue|yellow|green
```
els quals es mostraran en un combobox

o com una llista value:label
```
color=red:Vermell|blue:Blau|yellow:Groc|green:Verd
```

Si una llista només conté dos valors si aquests són exactament YES|NO o NO|YES, es renderitzarà com un checkbox essent el valor per defecte el del primer item de la llista.

Finalment, es pot iniciar un paràmetre com una llista, si s'escriu amb aquesta sintaxi
```
lallista=[primer;segon;tercer;quart]
```
Aquesta darrera definició es tradueix en javascript com un vector d'Strings. És interessant utilitzar-la amb el built-in helper de Handlebars, #each. Per exemple:

```
Els colors són
<ul>
{{#each lallista}}
  <li>{{this}}</li>
{{/each}}
</ul>
```

## Presets
El directori presets/ conté un conjunt d'snippets predefinits per a l'IEDIB. Es troben en format JSON i podn ésser importats des del menú de configuració de l'snippet.

## Presets raw
El directori presets_raw/ conté els mateixos snippets que la carpeta anterior però en format de text pla. Podeu fer servir aquesta opció si voleu crear els snippets manualment amb copia-aferra.

## Estils
El directori styles conté el full d'estils css que s'ha d'incloure al tema moodle. És recomanable que utilitzeu la versió comprimida (.min.css).  


## Installation
If using zip, first download and unzip the file, and place the snippet folder in your Moodle's /lib/editor/atto/plugins folder. Or use git directly from the /lib/editor/atto/plugins folder, ie
git clone https://github.com/IEDIB/iedib-atto_snippet.git 

Then visit your site admin's notifications page. The plugin installer will walk through the installation process. At the end you will see a list of 5 templates. You can leave them for now and fill them in later.  3 of the templates are blank, and a helloworld and a youtube template are automatically created for you.

Before the Snippet icon will be visible in the Atto toolbar you will need to visit site admin -> plugins -> text editors -> atto -> atto toolbar settings, and add "snippet" to the list of icons to display. There is a text area towards the bottom of the page for this purpose.

## Versions

**Versió 1.1.0** Plugin original

**Versió 1.1.2** (03/12/2018) Primer lliurament del plugin modificat

**Versió 1.1.3** (19/12/2018):

1. **Novetats**: (*nous snippets*)

- **Taula predefinida**: crea una taula de forma senzilla.
- **Graella d'imatges**: crea una figura que contingui moltes sub-imatges distribuïdes en forma de graella.
- **Capsa introducció**: introdueix una capsa destinada a introduir un lliurament o apartat del llibre de teoria.
- **Capçalera examen**: crea la capçalera d'un examen model col·locar a la pàgina principal de l'aula virtual.

2. **Millores**: (*modificacions a alguns snippets existents*)
- **Inserir imatge**: Ara es crea automàticament una imatge per defecte que es pot canviar fàcilment fent un doble-click sobre ella.
- **Youtube, Vimeo**: Es possible escapçar una part del video mitjançant la introducció d'un temps d'inici i fi mesurat en segons. *Atenció*: Vimeo no permet establir un temps d'acabament.
- **Capses**: Totes les capses tenen ara un paràmetre anomenat `$lang` (idioma). L'opció triada es desa per futurs usos de l'snippet.  
- **Dues columnes**: És possible canviar l'amplada relativa de cadascuna d'elles. Es pot canviar el paràmetre `AMPLADA_COL1` entre 1 a 11. El valor 6 fa que les dues columnes tinguin igual amplada. Com major és `AMPLADA_COL1 `, major és l'amplada de la primera columna.


3. **Millores**: (*a nivell de menú d'usuari*)
- Els botons snippet s'organitzen per categories.


**Versio 1.1.6 (17/07/2019)**
- Permet funcionar en mode de Desenvolupament (DEBUG) de moodle. Mostra sortida per consola.
- Les classes css del plugin és independent del tema. Petites millores visuals.
- Permet incloure tags <script></script> en els snippets, però en aquests no és possible interpolar variables.

- Les classes dels fitxers d'estil en style/iedib-avirtual-stylesheet-div37.css s'han fet independents de la versió de bootstrap

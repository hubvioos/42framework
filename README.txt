42framework est un outil de développement web, codé en PHP.
Il nécessite PHP 5.3 minimum pour fonctionner.

L'architecture d'une application codée à l'aide de 42framework est de
type HMVC. C'est-à-dire un ensemble de modules MVC, indépendants les uns
des autres.

Pour faire communiquer les modules entres eux, le développeur pourra utiliser la classe Request, qui permet de créer et d'exécuter une action.
Syntaxe : Request::factory($module, $action, $params, $state)->execute()

Un système de "routes" permet d'utiliser des urls personnalisées,
sans avoir à plier son application à une structure particulière.

Un outil en CLI permet d'effectuer certaines actions, comme la
compilation de la configuration de l'application et de l'autoload.

Pour les fonctionnalités de gestion multi-serveur prévues dans le cahier
des charges, une classe ExternalRequest permet d'envoyer une requête
à un serveur distant. Après réflexion, il a semblé peu judicieux de
coder un tel système en PHP. Il existe une librairie permettant ce genre
de chose : gearman. Un module permettant de créer simplement des
"entités" gearman est à l'étude pour une prochaine version.

Une documentation est en cours de rédaction.

42framework est disponible en téléchargement à l'adresse suivante :
http://github.com/Ingolmo/42framework (branche reorga).

Le développement n'est pas terminé et le projet continuera à évoluer dans les prochains mois. Cependant, la version actuelle est fonctionnelle et peut donc être utilisée.

Pour toutes informations supplémentaires ou remarques, contactez :
contact@42framework.com
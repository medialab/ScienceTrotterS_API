<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Credits;

class CreditsController extends Controller
{
	protected $bAdmin = false;
	protected $sModelClass = 'Credits';

	protected $CGU = array(
		"fr" => "
		<p>Les présentes conditions générales d’utilisation (ci-après dénommées « CGU ») déterminent les conditions d’accès et d’utilisation par tout utilisateur (ci-après l’« Utilisateur ») de toutes données, éléments, contenus accessibles depuis la présente application (ci-après dénommée l’ « Application»)  ainsi que de l’Application en elle-même.
		Tout accès à l’Application implique l'acceptation des présentes Conditions Générales d'Utilisation, sans aucune réserve.</p>
		<p>L'Utilisateur reconnaît avoir, préalablement à l’utilisation de l’Application pris connaissance de l'ensemble des présentes CGU énoncées et déclare les accepter sans réserve. Il reconnaît en outre avoir la capacité de conclure.</p>	
		<p>Les présentes CGU prévaudront sur toutes autres conditions figurant dans tout autre document, sauf dérogation expresse et écrite.</p>
		<p>L’Editeur se réserve le droit de modifier les CGU à tout moment. Il est donc conseillé à l'Utilisateur de se référer, régulièrement à la dernière version des CGU en ligne.</p>
		
		<h2 class='titleCredits'>PREAMBULE</h2>
		<p>SCIENCE TROTTERS est une application répondant au besoin de se familiariser avec les processus de production des connaissances scientifiques et les incertitudes qui y sont associées. C’est le projet des humanités scientifiques, via un format inédit : la diffusion de contenus pédagogiques principalement sous forme de podcasts à travers des points d’intérêts et des parcours. Cette application a été créée sur une idée originale de Nicolas Benvegnu.</p>
		<p>SCIENCE TROTTERS a été développé dans le cadre du programme FORCCAST, 2019, programme financé par l’Agence Nationale de la Recherche. Référence du Projet : ANR-11-IDEX-0005-02.</p>
		<p>L’équipe-projet à l’origine du développement de SCIENCE TROTTERS est citée ci-dessous :</p>
		<p>Format et contenus pédagogiques : Nicolas Benvegnu, Pamela Schwartz, Thomas Tari</p>
		<p>Chef de projet : Antoine Trouche</p>
		<p>Identité/Architecture de l’interface : Estelle Chauvard</p>
		<p>Consultants techniques : Paul Girard, Robin de Mourat</p>
		<p>Consultant design : Donato Ricci</p>
		<p>Voix : Benoît Celotto, Esmeralda Kroy</p>
		<p>L’enregistrement audio a eu lieu dans le studio de Sciences Po avec l’aide du service audiovisuel (chef de projet : Maxime Crépel).</p>
		
		<h2 class='titleCredits'>1. MENTIONS LÉGALES</h2>
		<p>Editeur :</p>
		<p>La Fondation Nationale des Sciences Politiques, dont le siège est situé au 27, rue Saint Guillaume 75337 Paris Cedex 07 – N° Siren : 784 308 249.</p>
		<p>Directeur de Publication :</p>
		<p>Le directeur de la publication du site web est Frédéric Mion, Administrateur de la Fondation Nationale des Sciences Politiques (FNSP).</p>
		<p>Hébergeur :</p>
		<p>L’hébergement du Site est assuré par la Direction des systèmes d’information de la FNSP.</p>
		
		<h2 class='titleCredits'>2. DISPOSITIONS GENERALES</h2>
		<p>L’Editeur fait ses meilleurs efforts pour offrir aux Utilisateurs des informations et/ou outils disponibles et vérifiés. Malgré tous les soins apportés, les informations sont fournies sans garantie d'aucune sorte.</p>
		<p>Ces informations sont non contractuelles, peuvent contenir des inexactitudes techniques ou typographiques et sont sujettes à modification sans préavis. L'Éditeur ne saurait être tenu pour responsable des erreurs, d’une absence de disponibilité des informations, exactitudes, mises à jour, complétudes et/ou de la présence d’un virus sur son site.</p>
		<p>L'Éditeur invite les Utilisateurs du site à lui faire part d’éventuelles omissions, erreurs ou corrections, en adressant un courrier électronique à l’adresse forccast.controverses@sciencespo.fr</p>
		<p>De même, l'Éditeur ne peut être tenu responsable en cas de mauvaise utilisation du service par l’Utilisateur ou en cas d’indisponibilité temporaire du service (cas de force majeure, de période de maintenance ou d’incident technique, quel qu’il soit).</p>
		<p>Il est expressément entendu par l'Utilisateur de ce site qu'en aucun cas l’Editeur ne peut être tenu responsable des dommages quelconques, directs ou indirects, matériels ou immatériels résultant notamment de la consultation et/ou de l'utilisation de ce site web (ou d'autres sites qui lui sont liés) et des éventuelles applications en téléchargement, comme de l'utilisation des informations textuelles ou visuelles, qui auraient pu être recueillies et notamment de tout préjudice financier ou commercial, de pertes de programmes ou de données dans son système d'information.</p>
		<p>L’Utilisateur du site reconnaît disposer de la compétence et des moyens nécessaires pour accéder à et utiliser l’Application.</p>
		<p>L'Utilisateur du site internet reconnaît avoir vérifié que la configuration informatique utilisée ne contient aucun virus et qu'elle est en parfait état de fonctionnement.</p>

		<h2 class='titleCredits'>3. DISPONIBILITÉ DE L’APPLICATION</h2>
		<p>L'Éditeur et/ou ses fournisseurs pourra(ont), à tout moment, modifier ou interrompre, temporairement ou de façon permanente, tout ou partie de l’accès à l’Application. L’Editeur ne pourra être tenu responsable de toute modification, suspension ou interruption de l’Application.</p>
		
		<h2 class='titleCredits'>4. PROPRIÉTÉ INTELLECTUELLE</h2>
		<p>La structure générale, la charte graphique de l’Application, ainsi que les textes, images animées ou non, vidéos, sons tels que podcasts, savoir-faire, dessins, graphismes (…) et tout autre élément composant l’Application, sont la propriété exclusive de l'Éditeur, sous réserve de droit de tiers.</p>
		<p>Il en est de même des éventuelles bases de données contenues au sein de l’Application qui sont protégées par les dispositions de la loi du 1er juillet 1998 portant transposition dans le Code de la propriété intellectuelle de la directive européenne du 11 mars 1996 relative à la protection juridique des bases de données, et dont l’Editeur est producteur.</p>
		<p>L’Utilisateur reconnaît que les marques de l'Éditeur et de ses partenaires, ainsi que les logos figurant sur le site, peuvent être des marques déposées protégeables par le livre 7 du code de la propriété intellectuelle.</p>
		<p>Sauf accord préalable et écrit de l'Éditeur, toute utilisation ou reproduction, totale ou partielle, du site, des éléments qui le composent et/ou des informations qui y figurent, par quelque procédé que ce soit, est strictement interdite et constitue une contrefaçon sanctionnée par le Code de la propriété intellectuelle.</p>
		<p>L’Editeur se réserve le droit de poursuivre tout Utilisateur pour tout acte de contrefaçon de ses droits de propriété intellectuelle.</p>
		
		
		<h2 class='titleCredits'>5. DONNÉES PERSONNELLES</h2>
		<p>L’Application ne nécessite pas la gestion et/ou le stockage de données à caractère personnel au sens du Règlement général sur la protection des données 2016/679 et la loi n°78-17 du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés modifiée en 2004.</p>
		
		<h2 class='titleCredits'>6. STOCKAGE DES DONNÉES</h2>
		<p>Pour le bon fonctionnement de l’Application, l’Utilisateur est averti que des données d’utilisation (tels que le choix de la langue, la taille des caractères, des fichiers audios et photos des points d’intérêts) seront stockés au sein de la mémoire téléphone de l’Utilisateur. Ce stockage permettra notamment à l’Utilisateur d’accéder à ces données hors connexion.</p>
		
		<h2 class='titleCredits'>7. LIENS HYPERTEXTES</h2>
		<p>L’Application peut contenir des liens hypertextes vers d’autres sites web ou application.</p>
		<p>La responsabilité de l’Editeur ne saurait cependant être engagée au titre d’un site tiers auquel l’Utilisateur a eu accès via l’Application et qui présenterait des contenus illicites ou inexacts.</p>
		<p>En accédant à un autre site, par l'intermédiaire d'un lien hypertexte, vous acceptez que cet accès s'effectue à vos risques et périls. En conséquence, tout préjudice direct ou indirect résultant de votre accès à un autre site relié par un lien hypertexte ne peut engager la responsabilité de l'Éditeur.</p>
		
		<h2 class='titleCredits'>8. LOI APPLICABLE ET JURIDICTION COMPETENTE</h2>
		<p>Les CGU sont soumises au droit français.</p>
		<p>Tout litige relatif aux CGU relèvera de la compétence des juridictions françaises.</p>
		" ,
		"en" => "
		<p>These general terms and conditions of use (hereinafter the « GT ») govern the conditions of access and use of the science trotter app including all data and contents available from the app (hereinafter the « App ») by any user (hereinafter the « User »).</p>

		<p>Before using the App, you acknowledge that you have read all provisions of the GT and you signify your agreement to be bound by it. You also declare that you have the capacity to give your agreement.</p>
		
		<p>The GT prevails on any other provisions or agreement from any document, except if Editor give express written consent.</p>
		
		<p>The Editor may change, add or remove portions of these GT at any time, which shall become effective immediately upon posting. It is your responsibility to review these GT prior to each use of the App and by continuing to use this App, you agree to any changes.</p>
		
		
		<h2 class='titleCredits'>RECITAL</h2>
		
		<p>SCIENCE TROTTERS is an app consisting in the issuance of educational contents to User, regarding the history of sciences of several cities, mainly by the way of podcast in the frame of a city tour outside the university.</p>
		
		<p>SCIENCE TROTTERS has been developed as part of FORCCAST program, 2019, funded by Agence Nationale de la Recherche. Program Ref : ANR-11-IDEX-0005-02.</p>
		
		<p>The team of the program is described below :</p>
		
		<p>Format and educational contents : Nicolas Benvegnu, Pamela Schwartz, Thomas Tari</p>
		<p>Project officer : Antoine Trouche</p>
		<p>Identity/Interface design : Estelle Chauvard</p>
		<p>Technical consultant : Paul Girard, Robin de Mourat </p>
		<p>Design consultant : Donato Ricci</p>
		<p>Voices : Benoit Celotto, Esmeralda Kroy</p>
		<p>All texts have been recorded in the Sciences Po studio, with assistance from the audiovisual department (project manager : Maxime Crépel)</p>
		
		
		<h2 class='titleCredits'>1. LEGAL NOTICES</h2>
		<p> </p>
		<p>Editor :</p>
		<p>Fondation Nationale des Sciences Politiques, with registered offices located at 27, rue Saint Guillaume 75337 Paris Cedex 07 – Siren : 784 308 249.</p>
		
		<p>Publishing director :</p>
		<p>The publishing director of the App is Frédéric Mion, administrator of Fondation Nationale des Sciences Politiques (FNSP).</p>
		
		<p>App host :</p>
		<p>The hosting of the site is managed by the IT system division of FNSP.</p>
		
		
		<h2 class='titleCredits'>2. GENERAL PROVISION</h2>
		
		<p>The Editor makes its best efforts to offer checked and available information. Nonetheless, the Editor does not represent or endorse the accuracy or reliability of any content, or other information displayed, uploaded, or distributed through the App. </p>
		
		<p>You acknowledge that any reliance upon any such contents, or information shall be at your sole risk. The App and all downloadable software are distributed « as is » basis without warranties of any kind, either express or implied, including without limitation, warranties or title or implied warranties of merchantability or fitness for a particular purpose. You hereby acknowledge that use of the App is at your sole risk.</p>
		
		<p>You can contact the Editor at forccast.controverses@sciencespo.fr in order to inform about any mistakes or failures.</p>
		
		<p>The Editor shall not be liable for any misuse of the App by the User or for any temporary unavailability of the App.</p>
		
		<p>You acknowledge that you have the appropriate background and means to access and use the App.</p>
		
		
		<h2 class='titleCredits'>3. AVAILABILITY OF APP AND CONTENTS RELATED</h2>
		<p> </p>
		<p>The Editor may change, suspend or discontinue any aspect of the App at any time, including the availability of any feature, database, or content. The Editor may also impose limits on certain features and services or restrict your access to parts or all of the App without notice or liability.</p>
		
		<p>The Editor shall not be liable of any change, suspension or discontinuation of the App</p>
		
		
		<h2 class='titleCredits'>4. INTELLECTUAL PROPERTY</h2>
		<p> </p>
		<p>All materials published on the App (including, but not limited to scripts, photographs, images, illustrations, audio clips and video clips) and graphic charter of the App are protected by copyright, and owned or controlled by the Editor.</p>
		
		<p>All databases available from the App are protected by the provisions of the laws of July, 1st, 1998 transposing the EU directive of March, 11th, 1996 regarding the protection of database. You acknowledge that the Editor is the database producer of all database available from the App.</p>
		
		<p>You also acknowledge that all graphics, logos, scripts, and service names included in or made available through App can be registered as Trademark by the Editor.</p>
		
		<p>You may not extract and/or re-utilise parts of the content of the App without Editor express written consent. You may also not create and/or publish your own database that features substantial parts of the App without Editor express written consent.</p>
		
		<p>Editor has the right to pursue any Users for counterfeit of its intellectual property rights.</p>
		
		
		<h2 class='titleCredits'>5. PERSONAL DATA</h2>
		<p> </p>
		<p>No personal data (in the meaning of GDPR 2016/679 and the law 78-17) is used and stored as part of the running of the App.</p>
		
		
		<h2 class='titleCredits'>6. STORAGE OF DATA</h2>
		
		<p>For the normal use of the App, you are aware that the data used by the App (such as language option, characters size, audio files and photos) is only stored inside your phone. This storage is useful to access in the App when your phone is offline.</p>
		
		
		<h2 class='titleCredits'>7. HYPERTEXTES LINKS</h2>
		
		<p>The App may contain hypertext links that take you to other sites.</p>
		<p>The Editor shall not be liable for any breach of current legal or regulatory provisions by these sites.</p>
		
		<p>You acknowledge that access is made at your sole risk without liability of the Editor.</p>
		
		
		<h2 class='titleCredits'>8. APPLICABLE LAW AND JURISDICTION</h2>
		
		<p>The GT shall be construed in accordance with and governed by the laws of France.</p>
		<p>Any litigation between User and Editor relating to the existence, the validity, the construction, the execution and termination of this GT – or any of its provisions –, which the Parties could not solve amicably, shall be submitted to the court of French competent jurisdiction.</p>
		"
	);


	public function latest(Request $oRequest) {
		
		return $this->sendResponse(array('content' => $this->CGU));
	}
}
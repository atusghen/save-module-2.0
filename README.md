## SAVE TOOL Thesis Project

<img src="https://github.com/fabiomor/save-module-2.0/assets/39970186/962695d5-abe1-479e-a27b-954fb2808444" width="230"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <img src="https://github.com/fabiomor/save-module-2.0/assets/39970186/6bcb3657-f996-4a7e-8093-7608093e60ce" width="200">

Questa repository contiene il modulo SAVE integrato all'interno della piattaforma [PELL](https://www.pell.enea.it/), di proprietà di ENEA, per il supporto alla valutazione economica degli investimenti di riqualificazione.

In particolare consente ad un utente di valutare un possibile investimento nel rifacimento dell'impianto di illuminazione pubblica tramite una serie di metriche e valutazioni che questo modulo si occupa di calcolare e fornire all'interfaccia front-end del portale.

In questa tesi è possibile consultare un lavoro di definizione dei requisiti, applicazione di modifiche alla base di dati, sviluppo delle funzionalità e testing delle stesse.

Essa è consultabile nella directory `/doc` di questa repository, insieme a tutti gli artefatti utilizzati per la composizione della tesi:

[Estensione del modulo della piattaforma ENEA PELL per la valutazione economico-finanziaria degli impianti di illuminazione pubblica](https://github.com/fabiomor/save-module-2.0/blob/main/doc/Tesi%20ENEA%20SAVE%20greco%20gamba%202023.pdf)
## Obiettivo del servizio

Lo scenario di riferimento per il modulo applicativo è relativo ad amministratori di enti locali che, in fase di pianificazione di interventi di riqualificazione illuminotecnica di un impianto di pubblica illuminazione, procedono ad un censimento dell’infrastruttura, alla quantificazione di costi e/o benefici e successivamente ad una analisi preliminare delle diverse modalità di finanziamento per proseguire con il suddetto intervento di riqualificazione, attraverso l’uso di linee guida qualitative che mirano a supportare gli amministratori locali nella scelta tra queste diverse modalità di finanziamento, a seconda delle condizioni in cui si trova l’ente di appartenenza.

## Tecnologie utilizzate

* PHP/Laravel 
* MySQL/PHPMyAdmin 
* diagrams.net

## Procedura di installazione ed esecuzione

Come ambiente di sviluppo è stato utilizzato [WAMP server](https://www.wampserver.com/en/), che è necessario pre-installare prima di clonare questa repository. Successivamente 
1. Si può controllare la corretta installazione di WAMP lanciando da shell il comando `php --version`
2. Clonare nella cartella di installazione di WAMP `C:\wamp64\www` questa repository con il comando `git clone <repo>`
3. Dare la configurazione iniziale al framework Laravel/PHP editando il file `.env` preconfigurato oppure `.env.example` di default. In particolare:

       Bisogna creare un database vuoto con un nome a piacere (utilizzeremo `save`) accedendo a PHPMyAdmin all'indirizzo `http://localhost/phpmyadmin/`
       Successivamente inserire nel proprio `.env` le credenziali di accesso al MySQL che se non sono state configurate sono `DB_DATABASE=save DB_USERNAME=root DB_PASSWORD= `
5. eseguire infine un `composer install` per installare tutte le dipendenze di Laravel

A questo punto è possibile eseguire l'app web con i comandi:

`php artisan key:generate`

`php artisan optimize`

`php artisan serve`

Se tutto è stato svolto correttamente, all'indirizzo http://127.0.0.1:8000/ è possibile osservare la pagina di benvenuto di Laravel 8 ed è ora possibile chiamare le API REST del modulo SAVE

## Presentazione del portale

L'interfaccia del modulo SAVE si può considerare suddivisa in due parti: la prima parte si occupa dell'inserimento, della gestione e della visualizzazione dei dati di impianti, zone omogenee, cluster e investimenti, mentre la seconda parte si occupa di mostrare in modo chiaro e ordinato i risultati ottenuti dalle varie analisi economico finanaziarie svolte.
L'interfaccia della visualizzazione dei risultati è divisa in sezioni distinte:
1. La prima sezione contiene il risultato del calcolo dei flussi di cassa per ogni coppia di zone omogenee dell'impianto scelto, unito con i parametri dell'investimento scelto.
2. La seconda sezione contiene i risultati dei calcoli relativi al VAN e al TIR, con la possibilità di cambiare alcuni parametri e di ricalcolare i risultati.
3. La terza sezione contiene i risultati del calcolo del tempo di Payback dell'investimento, con la possibilità di cambiare il costo unitario di energia e controllare direttamente l'impatto delle modifiche effettuate.
4. La quarta sezione contiene il canone minimo e massimo da corrispondere a un eventuale soggetto privato, con la possibilità di variare alcuni parametri e di visualizzare istantaneamente le modifiche ai risultati.

![interfaccia full](https://github.com/fabiomor/save-module-2.0/assets/64854693/d6260e9c-073f-4bb3-83df-17e594ae60ce)




 > Un ringraziamento alla prof.ssa Patrizia Scandurra, nonchè ai correlatori Fabio Moretti ed Edoardo Scazzocchio per averci guidato durante lo sviluppo di questa tesi.



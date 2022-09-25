# Digitaltolk-code-review

## Code Analysis

* After my analysis on code it shows that code is missing some best practices, separation of concerns. Controller have too much code which makes it quite messy
and difficult to understand by a new dev. each function/class should only have one resposibility. Request validation must be handled by its separate Request classes
because less functionalities in a single class will have fewer dependencies.

* Also if we are following repository pattern all the business logics should shift to repositories rather than writing it on controllers. controller should only
responsible to call the repository with required data and pass data to view or response in json.

* We can add Service classes also for business logics, repository should contain the DB queries because infuture if we want to change the DB we can add new repository to
write the queries as per new database and our business logic will not disturb.

* Setting default values for variable and array values was done a lot. It would be nice to have a helper method just to set
default value for each of them instead of writing the same statement over and over.

* There is some undefined variables also which makes the code terrible.

* Warning/Error messages could be templated (via helper) instead of writing it's contents over and over again.

* Repository also have too much code which is quite difficult to understand in a glance, we can use laravel features for separate Notifications classes,
Job handlings, Email sending so our repository is clean from too much extra code.

* Email should be sent in queues as it takes time which is not good for user experience point of view

* Helpers function should move to Helper classes for reusability.

* It's better to use guzzlehttp/guzzle for calling external API.

* User related function needs to be inside its separate User Repository.

* Env variables should not call directly into code it should be defined in config then use it via config variables inorder to easily alter them infuture.
# Warranty Mobile Application

# Table of contents
* [General Info](#general-info)
* [Technologies](#technologies)
* [Back End](#back-end)
* [Api](#api)
* [Front End](#front-end)
* [Functionalities](#functionalities)

# General Info
Web site for Bicycle Atelier, nonprofit organization. Dev environment: Docker and Lando.

# Technologies:
* Symfony
* MySQL
* Flutter
* Docker
* Lando

# Back End
Backend and API developed in Symfony, Database in MySQL.
Creation of accounts, generation of JWT token and implementation of authentication system.
Access to data restricted according to each user, they can only access their data. Authentification sent in requests header to verify the user connected.
All passwords are hashed when saved in the database and they are never sent in the API responses.

# Api
API Rest developed without framework or API bundle, entities added to Api with the Symfony bundle Doctrine Annotation. 
Implementation of controllers CRUD for each API endpoint. Data fetched from Database from queries in the repositories.
Private Api with authentification to fetch, delete, modify or add new data. 

# Front End
Front end developed in React and styled with SCSS. Development of components and requests made with library Axios.

## Functionalities 
* Creation of accounts
* Equipments management
* Management of warranties associated with equipments 
* Management of manufactureres and documents associated with warranties
* Historic management
 

# Swordfish 2020
##### Swordfish formal applicant test.

## Preface
Greetings; thank you for your time to asses this assignment.

Best regards,
Allie.

### Premise
- Challenge: small web application that uses the Github API and OAuth to view a list of issues and create new issues.
- Two tasks are specified: 
    - Task 1: List issues
        - Retrieve all issues (open and closed) from the repository and display 
    - Task 2: Write issues
        - The user should be able to provide the following information when submitting a new issue:
        - Issue Title
        - Issue Description (Body)
        - Select which client this issue is related to (C: Client ABC, C: Client XYZ, C: Client MNO)
        - Select a priority for the issue (P: Low, P: Medium, P: High)
        - Select the type of the issue (T: Bug, T: Support, T: Enhancement)
- **Important Information**.
    - OAuth to authenticate your web application.
    - Email iwant2work@swordfish.co.za providing us with the
        - IP Address or domain name, 
        - your OAuth callback URL and your 
        - Github Username
    

### Objectives
- Integrate with Github API v3 via OAuth.
- Create class definitions to represent the Github entities.
    - Write class interface.
    - Implement class methods.
- Build web interface.

### Focus
- Simplicity and readability of code.
- Exception mitigation.
- Ease of use.
- Verbosity of code comments.
 
## Predicates
- Public domain to be used: allie.co.za
- No state/database necessary.
- PHP 7.3+
- Vanilla code style; illustrating ability to un-cook spaghetti.
- Personal Github account: AllieTheDemonHunter
- No guide or manual falls in the scope.
- No writing of extensive repository commit messages.
- Unit/Functional testing excepted. 

#### Exception Handling
- Indicated as imperative this assignment.
    - Semantic user messages upon fatal errors. (Nice to have)
    - Limit to sufficient catch clauses, as not to overcrowd the app.

#### Ui/Ux
- Allocating a maximum percentage of time to 25% for styling.
- No frontend layout framework except, an html boilerplate stylesheet and jQuery.
- Mobile/responsiveness not imperative, but a mobile first approach to be held to.

## Given / Provided
Three Prefixes P, C, T
- P: Priority - (P: Low, P: Medium, P: High)
- C: Client -  (C: Client ABC, C: Client XYZ, C: Client MNO)
- T: Types - (T: Bug, T: Support, T: Enhancement)
 
## Concepts
Nine labels, prefixed with P, C or T.

### Contraindications with regards to C-type lables.
- Nine lables mean that there are nine entities categorised with three prefixes.
- This could mean that C-prefixed labels are many-to-*
- How could there only be nine lables, when one of their prefixes is to identify a client?

# Running this file initializes the server
# It does the following:
# - checks if all roles have an officer, all event_categories have a role, and alerts if they do not
# - creates a publicly available json file for event_categories.json

import json

with open("/var/www/html/event_categories.json", "r", encoding="utf-8") as event_categories_file, open("/var/www/html/officers.json", "r", encoding="utf-8") as officers_file, open("/var/www/html/roles.json", "r", encoding="utf-8") as roles_file, open("/var/www/html/event_categories_public.json", "w", encoding="utf-8") as event_categories_public_file:

    event_categories = json.loads(event_categories_file.read())
    officers = json.loads(officers_file.read())
    roles = json.loads(roles_file.read())

    # check roles
    unmatched_roles = []
    for role in roles:
        unmatched_roles.append(role)
    for officer in officers:
        unmatched_roles.remove(officer["role"])
    if len(unmatched_roles) != 0:
        print ("Alert: the following roles have no officer assigned to them: " + str(unmatched_roles))

    # check event categories
    unmatched_categories = []
    for evtcat in event_categories:
        unmatched_categories.append(evtcat)
    for role in roles:
        for evtcat in roles[role]["event_categories"]:
            unmatched_categories.remove(evtcat)
    if len(unmatched_categories) != 0:
        print ("Alert: the following event categories have no role assigned to them: " + str(unmatched_categories))

    # create public event categories file
    public_event_categories = {}
    for evtcat in event_categories:
        public_event_categories[evtcat] = {
            "child_categories": event_categories[evtcat]["child_categories"][:]
        }
    event_categories_public_file.write(json.dumps(public_event_categories))


/* This is an utility
 * convert snippets from ../presets to a readable representation 
 * execute me with nodejs */

const fs = require("fs");
const path = require("path");

const directory = "../presets/";

const dirStruct = fs.readdirSync(directory);
dirStruct.forEach((f)=>{
    if (f.endsWith(".txt")) {
        const json = fs.readFileSync(path.join(directory, f), "utf-8");
        let parsed;
        try{
            parsed = JSON.parse(json);
        } catch (Ex){
            console.log("Invalid JSON for file "+ f + ":: ", Ex)
        }
        if(parsed) {
        const output = [];
        output.push("** NAME: "+parsed.name+"----------------------------------------------------");
        output.push("");
        output.push("** KEY: "+parsed.key+"----------------------------------------------------");
        output.push("");
        output.push("** INSTRUCTIONS: -----------------------------------------------------------------");
        output.push(parsed.instructions);
        output.push("");
        output.push("** DEFAULTS: -----------------------------------------------------------------");
        output.push(parsed.defaults);
        output.push("");
        output.push("** BODY: -----------------------------------------------------------------");
        output.push(parsed.body.replace(/\\n/g, "\n"));
        output.push("");
        output.push("** VERSION: "+parsed.version+"----------------------------------------------------");
     
        fs.writeFileSync(path.join("./", f), output.join("\n"));
        }
    }
}); 
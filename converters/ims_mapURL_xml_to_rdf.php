<?php

//input in $response in xml format 
//output $rdfData in NTriples format

//generate triples as: $inputConceptWiki skos:exactMatch $targetURLs

$xmlData = simplexml_load_string($response);
if ($xmlData===false){
    throw new ErrorException("Error. External service returned: ".$response);
}
if ($xmlData->count()==0){
    throw new EmptyResponseException("No results returned from the IMS");
}

$unreservedParameters = $this->Request->getUnreservedParams();
$inputURL = $unreservedParameters["Uri"];
foreach ($xmlData->{"targetUri"} as $mapping){
        $targetURL = (string)$mapping[0];
        $this->DataGraph->add_resource_triple($inputURL, SKOS.'exactMatch', $targetURL);
}

$rdfData = $this->DataGraph->to_ntriples();

//link pageUri to the primary topic - $inputURL
$this->DataGraph->add_resource_triple($this->pageUri, FOAF.'primaryTopic', $inputURL);
$this->DataGraph->add_resource_triple($inputURL , FOAF.'isPrimaryTopicOf', $this->pageUri);
$this->DataGraph->add_resource_triple($this->Request->getUri(), API.'definition', $this->endpointUrl);

?>

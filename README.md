# Neo4jBundle
Symfony integration for the [GraphAware Neo4j PHP Client](https://github.com/graphaware/neo4j-php-client)

## Configuration
```yaml
pandawan_technology_neo4j:
    connections:
        default: # The connection alias
            uri: "bolt://neo4j:neo4j@127.0.0.1" # The server URI
            # master: true # When dealing with multiple connections, you might want to turn this to false (default: true)
            # timeout: 5 # The connection timeout (default: 5)
```

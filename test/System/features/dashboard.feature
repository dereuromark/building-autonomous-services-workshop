Feature:
  The dashboard offers the manager of the warehouse an overview of all the relevant information.

  Scenario: Products that haven't been purchased yet
    Given the catalog has a product "Mars rover"
    Then I should see that "Mars rover" has a stock level of 0

  Scenario: A product that has been purchased
    Given the catalog has a product "Mars rover"
    And we have purchased and received 10 items of this product
    Then I should see that "Mars rover" has a stock level of 10

  Scenario: Auto-deliver a product that has been purchased and sold
    Given the catalog has a product "Mars rover"
    And we have purchased and received 10 items of this product
    And we have sold 5 items of this product
    Then I should see that "Mars rover" has a stock level of 5

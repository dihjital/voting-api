//
//  Question.swift
//  Voting
//
//  Created by Szabolcs Tóth on 15.10.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import Foundation

struct Question: Codable {
    let id: Int
    let questionText: String
    let numberOfVotes: Int
    
    enum CodingKeys: String, CodingKey {
        case id
        case questionText = "question_text"
        case numberOfVotes = "number_of_votes"
    }
}

/// Dummy data for building the interface
extension Question {
    static let dummyQuestion1 = Question(id: 1,
                                         questionText: "Et ratione architecto aperiam corporis qui aut. Aut rerum nam quia. Nihil odit odit rerum aliquam et atque.",
                                         numberOfVotes: 2)
    
    static let dummyQuestion2 = Question(id: 2,
                                         questionText: "Quas quo voluptatibus voluptatem et. Nihil aspernatur ullam et. At repellat illo ullam saepe doloribus.",
                                         numberOfVotes: 8)
    
    static let dummyQuestion3 = Question(id: 3,
                                         questionText: "Ipsam dolores dolorem perferendis necessitatibus vel velit est quas. Ab labore id qui omnis odit nam et quia.",
                                         numberOfVotes: 1)
    
    static let dummyQuestions = [dummyQuestion1, dummyQuestion2, dummyQuestion3]
}
